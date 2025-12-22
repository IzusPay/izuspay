<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    /**
     * Lista de notícias da associação
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $association = $user->association;

        if (! $association) {
            return redirect()->route('dashboard')->with('error', 'Associação não encontrada.');
        }

        $query = News::byAssociation($association->id)
            ->with('author')
            ->latest();

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $news = $query->paginate(10);

        $totalNews = News::byAssociation($association->id)->count();
        $publishedNews = News::byAssociation($association->id)->published()->count();
        $draftNews = News::byAssociation($association->id)->draft()->count();

        return view('associacao.news.index', compact('news', 'totalNews', 'publishedNews', 'draftNews'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return view('associacao.news.create');
    }

    /**
     * Salvar nova notícia
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $association = $user->association;
        $creatorProfile = $user->creatorProfile;

        if (! $association || ! $creatorProfile) {
            return back()->with('error', 'Associação ou perfil do criador não encontrado.');
        }

        $data = $request->all();
        $data['association_id'] = $association->id;
        $data['user_id'] = $user->id;
        $data['creator_profile_id'] = $creatorProfile->id;
        $data['is_featured'] = $request->has('is_featured');
        $data['is_private'] = $request->has('is_private');

        // Processar tags
        if ($request->filled('tags')) {
            $data['tags'] = array_map('trim', explode(',', $request->tags));
        }

        // Upload da imagem
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('news', 'public');
        }

        // Definir data de publicação
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        News::create($data);

        return redirect()->route('associacao.news.index')
            ->with('success', 'Notícia criada com sucesso!');
    }

    /**
     * Visualizar notícia
     */
    public function show(News $news)
    {

        return view('associacao.news.show', compact('news'));
    }

    /**
     * Formulário de edição
     */
    public function edit(News $news)
    {

        return view('associacao.news.edit', compact('news'));
    }

    /**
     * Atualizar notícia
     */
    public function update(Request $request, News $news)
    {

        $data = $request->all();
        $data['is_featured'] = $request->has('is_featured');
        $data['is_private'] = $request->has('is_private');

        // Processar tags
        if ($request->filled('tags')) {
            $data['tags'] = array_map('trim', explode(',', $request->tags));
        } else {
            $data['tags'] = null;
        }

        // Upload da nova imagem
        if ($request->hasFile('featured_image')) {
            // Deletar imagem anterior
            if ($news->featured_image) {
                Storage::disk('public')->delete($news->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('news', 'public');
        }

        // Gerenciar data de publicação
        if ($data['status'] === 'published' && $news->status !== 'published') {
            $data['published_at'] = now();
        } elseif ($data['status'] !== 'published') {
            $data['published_at'] = null;
        }

        $news->update($data);

        return redirect()->route('associacao.news.index')
            ->with('success', 'Notícia atualizada com sucesso!');
    }

    /**
     * Excluir notícia
     */
    public function destroy(News $news)
    {
        // Deletar imagem se existir
        if ($news->featured_image) {
            Storage::disk('public')->delete($news->featured_image);
        }

        $news->delete();

        return redirect()->route('associacao.news.index')
            ->with('success', 'Notícia excluída com sucesso!');
    }

    /**
     * Publicar/despublicar notícia
     */
    public function togglePublish(News $news)
    {

        if ($news->isPublished()) {
            $news->unpublish();
            $message = 'Notícia despublicada com sucesso!';
        } else {
            $news->publish();
            $message = 'Notícia publicada com sucesso!';
        }

        return back()->with('success', $message);
    }

    /**
     * Destacar/remover destaque da notícia
     */
    public function toggleFeatured(News $news)
    {

        $news->update(['is_featured' => ! $news->is_featured]);

        $message = $news->is_featured ?
            'Notícia adicionada aos destaques!' :
            'Notícia removida dos destaques!';

        return back()->with('success', $message);
    }
}
