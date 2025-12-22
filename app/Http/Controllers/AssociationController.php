<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\CreatorProfile;
use App\Models\Documentation;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\UserPerfilModel;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AssociationController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register-association');
    }

    public function register(Request $request)
    {
        $request->validate([
            // Dados da Association
            'tipo' => 'required|in:pf,cnpj',
            'nome' => 'required|string|max:255',
            'documento' => [
                'required',
                'string',
                Rule::unique('users', 'documento'),
                Rule::unique('associations', 'documento'),
                function ($attribute, $value, $fail) use ($request) {
                    $doc = preg_replace('/[^0-9]/', '', $value);
                    if ($request->tipo === 'pf' && strlen($doc) !== 11) {
                        $fail('O CPF informado é inválido.');
                    } elseif ($request->tipo === 'cnpj' && strlen($doc) !== 14) {
                        $fail('O CNPJ informado é inválido.');
                    }
                },
            ],
            'email' => 'required|email|unique:users,email|unique:associations,email',
            'telefone' => 'required|string|max:20',
            'cep' => 'required|string|max:9',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'bairro' => 'required|string|max:100',
            'cidade' => 'required|string|max:100',
            'estado' => 'required|string|size:2',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            // 1. Criar a Association (Perfil do Criador)
            $association = Association::create([
                'nome' => $request->nome,
                'tipo' => $request->tipo,
                'documento' => $request->documento,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'endereco' => $request->endereco,
                'numero' => $request->numero,
                'bairro' => $request->bairro,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'cep' => $request->cep,
                'complemento' => $request->complemento,
                'site' => $request->website, // <CHANGE> Usando o website do creator profile
                'descricao' => $request->bio, // <CHANGE> Usando a bio do creator profile
                'status' => 'pendente',
                'numero_membros' => 0,
            ]);

            Wallet::create([
                'association_id' => $association->id,
                'balance' => 0,
                'gateway_id' => 1,
            ]);

            $defaultFees = [
                [
                    'payment_method' => 'pix',
                    'percentage_fee' => 4.99,
                    'fixed_fee' => 1.49,
                ],
                [
                    'payment_method' => 'withdrawal',
                    'percentage_fee' => 0.00,
                    'fixed_fee' => 5.00,
                ],
            ];

            // Cria um registro de taxa para cada método de pagamento.
            foreach ($defaultFees as $feeData) {
                $association->fees()->create($feeData);
            }
            // 2. Criar o User
            $user = User::create([
                'association_id' => $association->id,
                'name' => $request->nome,
                'email' => $request->email,
                'documento' => $request->documento,
                'telefone' => $request->telefone,
                'password' => Hash::make($request->password),
                'tipo' => 'cliente',
                'status' => 'ativo',
            ]);

            $creatorProfile = CreatorProfile::create([
                'user_id' => $user->id,
                'username' => $user->id,
                'display_name' => $user->id,
                'bio' => 'bio',
                'category' => 'tecnologia',
                'website' => 'https://www.google.com',
                'location' => $request->cidade.', '.$request->estado,
                'is_verified' => false,
                'is_active' => true,
                'followers_count' => 0,
                'following_count' => 0,
                'posts_count' => 0,
            ]);

            UserPerfilModel::create([
                'user_id' => $user->id,
                'perfil_id' => 2,
                'is_atual' => 1,
                'status' => 1,
            ]);

            $tipoConta = $request->tipo;

            $requiredDocumentTypes = DocumentType::where('is_required', true)
                ->where('is_active', true)
                ->get();

            foreach ($requiredDocumentTypes as $documentType) {
                if ($tipoConta === 'pf' && Str::contains($documentType->name, 'CNPJ')) {
                    continue;
                }

                Documentation::create([
                    'user_id' => $user->id,
                    'document_type_id' => $documentType->id,
                    'file_path' => null,
                    'status' => 'missing', // Define o status como 'Faltando'
                    'rejection_reason' => 'Documento ainda não enviado durante o cadastro.',
                ]);
            }

            DB::commit();

            return redirect()
                ->route('login')
                ->with('success', 'Cadastro realizado com sucesso! Seu perfil de criador foi criado e aguarda aprovação para fazer login.');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erro no cadastro de criador: '.$e->getMessage());

            return back()
                ->withErrors(['error' => 'Não foi possível realizar o cadastro. Por favor, tente novamente.'])
                ->withInput();
        }
    }
}
