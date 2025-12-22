<?php

namespace App\Enums;

class CategoriaProduto
{
    const ADMINISTRACAO = 1;

    const ANIMAIS = 2;

    const ARQUITETURA = 3;

    const ARTES = 4;

    const AUTOAJUDA = 5;

    const AUTOMOVEIS = 6;

    const BLOGS = 7;

    const CASA = 8;

    const CULINARIA = 9;

    const DESIGN = 10;

    const EDICAO = 11;

    const EDUCACIONAL = 12;

    const ENTRETENIMENTO = 13;

    const ESPORTES = 14;

    const FILMES = 15;

    const GERAL = 16;

    const HQ = 17;

    const IDIOMAS = 18;

    const INFORMATICA = 19;

    const MARKETING_DIGITAL = 20;

    const INVESTIMENTOS = 21;

    const JOGOS_CARTAS = 22;

    const JOGOS_ONLINE = 23;

    const JURIDICO = 24;

    const LITERATURA = 25;

    const MARKETING_REDE = 26;

    const COMUNICACAO = 27;

    const MEIO_AMBIENTE = 28;

    const MODA = 29;

    const MUSICA = 30;

    const PAQUERA = 31;

    const PESSOAS_DEFICIENCIA = 32;

    const PLUGINS = 33;

    const PRODUTIVIDADE = 34;

    const INFANTIS = 35;

    const RELATORIOS = 36;

    const RELIGIAO = 37;

    const ROMANCES = 38;

    const RPG = 39;

    const SAUDE = 40;

    const SCRIPTS = 41;

    const SEGURANCA = 42;

    const SEXOLOGIA = 43;

    const SNIPPETS = 44;

    const TURISMO = 45;

    public static function all(): array
    {
        return [
            self::ADMINISTRACAO => 'Administração e Negócios',
            self::ANIMAIS => 'Animais de Estimação',
            self::ARQUITETURA => 'Arquitetura e Engenharia',
            self::ARTES => 'Artes e Música',
            self::AUTOAJUDA => 'Auto-ajuda e Desenvolvimento Pessoal',
            self::AUTOMOVEIS => 'Automóveis',
            self::BLOGS => 'Blogs e Redes Sociais',
            self::CASA => 'Casa e Jardinagem',
            self::CULINARIA => 'Culinária, Gastronomia, Receitas',
            self::DESIGN => 'Design e Templates PSD, PPT ou HTML',
            self::EDICAO => 'Edição de Áudio, Vídeo ou Imagens',
            self::EDUCACIONAL => 'Educacional, Cursos Técnicos e Profissionalizantes',
            self::ENTRETENIMENTO => 'Entretenimento, Lazer e Diversão',
            self::ESPORTES => 'Esportes e Fitness',
            self::FILMES => 'Filmes e Cinema',
            self::GERAL => 'Geral',
            self::HQ => 'Histórias em Quadrinhos',
            self::IDIOMAS => 'Idiomas',
            self::INFORMATICA => 'Informática',
            self::MARKETING_DIGITAL => 'Internet Marketing',
            self::INVESTIMENTOS => 'Investimentos e Finanças',
            self::JOGOS_CARTAS => 'Jogos de Cartas, Poker, Loterias',
            self::JOGOS_ONLINE => 'Jogos de Computador, Jogos Online',
            self::JURIDICO => 'Jurídico',
            self::LITERATURA => 'Literatura e Poesia',
            self::MARKETING_REDE => 'Marketing de Rede',
            self::COMUNICACAO => 'Marketing e Comunicação',
            self::MEIO_AMBIENTE => 'Meio Ambiente',
            self::MODA => 'Moda e Vestuário',
            self::MUSICA => 'Música, Bandas e Shows',
            self::PAQUERA => 'Paquera, Sedução e Relacionamentos',
            self::PESSOAS_DEFICIENCIA => 'Pessoas com Deficiência',
            self::PLUGINS => 'Plugins, Widgets e Extensões',
            self::PRODUTIVIDADE => 'Produtividade e Organização Pessoal',
            self::INFANTIS => 'Produtos Infantis',
            self::RELATORIOS => 'Relatórios, Artigos e Pesquisas',
            self::RELIGIAO => 'Religião e Crenças',
            self::ROMANCES => 'Romances, Dramas, Estórias e Contos',
            self::RPG => 'RPG e Jogos de Mesa',
            self::SAUDE => 'Saúde, Bem-estar e Beleza',
            self::SCRIPTS => 'Scripts',
            self::SEGURANCA => 'Segurança do Trabalho',
            self::SEXOLOGIA => 'Sexologia e Sexualidade',
            self::SNIPPETS => 'Snippets (Trechos de Vídeo)',
            self::TURISMO => 'Turismo',
        ];
    }
}
