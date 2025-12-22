<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageAttachment;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileUploadService
{
    /**
     * Processa e salva um arquivo de mensagem
     */
    public function processMessageFile(Message $message, UploadedFile $file): MessageAttachment
    {
        // Valida o arquivo
        $this->validateFile($file);

        // Determina o tipo do arquivo
        $fileType = $this->determineFileType($file->getMimeType());

        // Gera nome único
        $fileName = $this->generateUniqueFileName($file);

        // Define diretório baseado no tipo e data
        $directory = $this->getUploadDirectory($fileType);

        // Salva o arquivo
        $filePath = $file->storeAs($directory, $fileName, 'public');

        // Prepara metadados
        $metadata = $this->extractMetadata($file, $fileType);

        // Gera thumbnail se necessário
        $thumbnailPath = $this->generateThumbnail($filePath, $fileType);

        // Cria registro no banco
        return MessageAttachment::create([
            'message_id' => $message->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $fileType,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'metadata' => $metadata,
            'thumbnail_path' => $thumbnailPath,
        ]);
    }

    /**
     * Valida se o arquivo pode ser enviado
     */
    private function validateFile(UploadedFile $file): void
    {
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $fileType = $this->determineFileType($mimeType);

        // Verifica tipo MIME permitido
        $allowedMimeTypes = config('filesystems.upload_settings.allowed_mime_types');
        $isAllowed = false;

        foreach ($allowedMimeTypes as $types) {
            if (in_array($mimeType, $types)) {
                $isAllowed = true;
                break;
            }
        }

        if (! $isAllowed) {
            throw new \InvalidArgumentException('Tipo de arquivo não permitido: '.$mimeType);
        }

        // Verifica tamanho do arquivo
        $maxSizes = config('filesystems.upload_settings.max_file_size');
        $maxSize = $maxSizes[$fileType] ?? $maxSizes['document'];

        if ($fileSize > $maxSize) {
            $maxSizeMB = round($maxSize / (1024 * 1024), 1);
            throw new \InvalidArgumentException("Arquivo muito grande. Tamanho máximo: {$maxSizeMB}MB");
        }
    }

    /**
     * Determina o tipo do arquivo baseado no MIME type
     */
    private function determineFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        return 'document';
    }

    /**
     * Gera nome único para o arquivo
     */
    private function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

        return Str::uuid().'.'.$extension;
    }

    /**
     * Obtém diretório de upload baseado no tipo e data
     */
    private function getUploadDirectory(string $fileType): string
    {
        $year = date('Y');
        $month = date('m');

        return "{$fileType}s/{$year}/{$month}";
    }

    /**
     * Extrai metadados do arquivo
     */
    private function extractMetadata(UploadedFile $file, string $fileType): array
    {
        $metadata = [];

        try {
            if ($fileType === 'image') {
                $imageInfo = getimagesize($file->getPathname());
                if ($imageInfo) {
                    $metadata['dimensions'] = [
                        'width' => $imageInfo[0],
                        'height' => $imageInfo[1],
                    ];
                    $metadata['format'] = $imageInfo['mime'] ?? null;
                }
            } elseif ($fileType === 'video') {
                // Para vídeos, seria necessário usar FFMpeg para extrair metadados
                // Por simplicidade, vamos apenas armazenar informações básicas
                $metadata['format'] = $file->getMimeType();

                // Se FFMpeg estiver disponível, pode extrair duração, dimensões, etc.
                // $metadata['duration'] = $this->getVideoDuration($file->getPathname());
            }
        } catch (\Exception $e) {
            // Se falhar ao extrair metadados, continua sem eles
            \Log::warning('Falha ao extrair metadados do arquivo: '.$e->getMessage());
        }

        return $metadata;
    }

    /**
     * Gera thumbnail para imagens e vídeos
     */
    private function generateThumbnail(string $filePath, string $fileType): ?string
    {
        if (! in_array($fileType, ['image', 'video'])) {
            return null;
        }

        try {
            $thumbnailSettings = config('filesystems.upload_settings.thumbnail_settings');
            $settings = $thumbnailSettings[$fileType];

            $thumbnailFileName = 'thumb_'.basename($filePath, '.'.pathinfo($filePath, PATHINFO_EXTENSION)).'.jpg';
            $thumbnailDirectory = 'thumbnails/'.dirname($filePath);
            $thumbnailPath = $thumbnailDirectory.'/'.$thumbnailFileName;

            // Cria diretório se não existir
            Storage::disk('public')->makeDirectory($thumbnailDirectory);

            if ($fileType === 'image') {
                return $this->generateImageThumbnail($filePath, $thumbnailPath, $settings);
            } elseif ($fileType === 'video') {
                return $this->generateVideoThumbnail($filePath, $thumbnailPath, $settings);
            }
        } catch (\Exception $e) {
            \Log::warning('Falha ao gerar thumbnail: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Gera thumbnail para imagem
     */
    private function generateImageThumbnail(string $filePath, string $thumbnailPath, array $settings): string
    {
        $fullFilePath = Storage::disk('public')->path($filePath);
        $fullThumbnailPath = Storage::disk('public')->path($thumbnailPath);

        // Usando Intervention Image (se disponível)
        if (class_exists('Intervention\Image\Facades\Image')) {
            $image = Image::make($fullFilePath);
            $image->fit($settings['width'], $settings['height'], function ($constraint) {
                $constraint->upsize();
            });
            $image->save($fullThumbnailPath, $settings['quality']);
        } else {
            // Fallback usando GD
            $this->generateThumbnailWithGD($fullFilePath, $fullThumbnailPath, $settings);
        }

        return $thumbnailPath;
    }

    /**
     * Gera thumbnail para vídeo
     */
    private function generateVideoThumbnail(string $filePath, string $thumbnailPath, array $settings): ?string
    {
        // Para gerar thumbnails de vídeo, seria necessário FFMpeg
        // Por simplicidade, vamos retornar null
        // Em produção, você implementaria algo como:

        /*
        if (class_exists('FFMpeg\FFMpeg')) {
            $ffmpeg = FFMpeg::create();
            $video = $ffmpeg->open(Storage::disk('public')->path($filePath));

            $frame = $video->frame(TimeCode::fromSeconds($settings['time']));
            $frame->save(Storage::disk('public')->path($thumbnailPath));

            return $thumbnailPath;
        }
        */

        return null;
    }

    /**
     * Gera thumbnail usando GD (fallback)
     */
    private function generateThumbnailWithGD(string $sourcePath, string $thumbnailPath, array $settings): void
    {
        $imageInfo = getimagesize($sourcePath);
        if (! $imageInfo) {
            throw new \Exception('Não foi possível obter informações da imagem');
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Cria imagem source
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new \Exception('Tipo de imagem não suportado para thumbnail');
        }

        // Calcula dimensões mantendo proporção
        $ratio = min($settings['width'] / $sourceWidth, $settings['height'] / $sourceHeight);
        $newWidth = intval($sourceWidth * $ratio);
        $newHeight = intval($sourceHeight * $ratio);

        // Cria thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled(
            $thumbnail, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $sourceWidth, $sourceHeight
        );

        // Salva thumbnail
        imagejpeg($thumbnail, $thumbnailPath, $settings['quality']);

        // Limpa memória
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
    }

    /**
     * Remove arquivo e thumbnail do storage
     */
    public function deleteFile(MessageAttachment $attachment): bool
    {
        $deleted = true;

        // Remove arquivo principal
        if (Storage::disk('public')->exists($attachment->file_path)) {
            $deleted = Storage::disk('public')->delete($attachment->file_path);
        }

        // Remove thumbnail se existir
        if ($attachment->thumbnail_path && Storage::disk('public')->exists($attachment->thumbnail_path)) {
            Storage::disk('public')->delete($attachment->thumbnail_path);
        }

        return $deleted;
    }

    /**
     * Obtém URL pública do arquivo
     */
    public function getFileUrl(MessageAttachment $attachment): string
    {
        return Storage::disk('public')->url($attachment->file_path);
    }

    /**
     * Obtém URL pública da thumbnail
     */
    public function getThumbnailUrl(MessageAttachment $attachment): ?string
    {
        if (! $attachment->thumbnail_path) {
            return null;
        }

        return Storage::disk('public')->url($attachment->thumbnail_path);
    }

    /**
     * Valida múltiplos arquivos
     */
    public function validateMultipleFiles(array $files): void
    {
        if (count($files) > 5) {
            throw new \InvalidArgumentException('Máximo de 5 arquivos por mensagem');
        }

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->validateFile($file);
            }
        }
    }

    /**
     * Processa múltiplos arquivos para uma mensagem
     */
    public function processMultipleFiles(Message $message, array $files): array
    {
        $this->validateMultipleFiles($files);

        $attachments = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $attachments[] = $this->processMessageFile($message, $file);
            }
        }

        return $attachments;
    }
}
