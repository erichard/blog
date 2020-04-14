@extends('_layouts.master')

@section('meta')
    <meta property="og:title" content="À propos | {{ $page->siteName }}"/>
    <meta property="og:url" content="{{ $page->getUrl() }}"/>
    <meta property="og:type" content="website" />
    <meta property="og:description" content="Développeur des années 90" />
    <meta property="og:image" content="{{ $page->baseUrl }}/assets/img/erwan.jpg" />
@endsection

@section('body')
    <h1>À propos</h1>

    <img src="/assets/img/erwan.jpg"
        alt="About image"
        class="flex rounded-full h-64 w-64 bg-contain mx-auto md:float-right my-6 md:ml-10">

    <p class="mb-6">Faisant partie de la génération 90', j'ai très tôt été attiré par les jeux vidéos et l'informatique en général. J'ai un souvenir mémorable d'après-midi passés à dicter à mon père le code de jeux pour Amstrad CPC 6128. Et c'est sur ce même Amstrad que j'ai écris mon premier programme en BASIC.</p>

    <p class="mb-6">Des années plus tard et plusieurs diplômes d'informatique dans la poche, je démarre ma carrière en 2006 dans l'équipe du <abbr title="Conservation National des Arts et Métiers">Cnam</abbr> pour développer la plateforme de formation à distance.</p>

    <p class="mb-6">Après quatre années de PHP maison, de PostgreSQL, de LDAP et de Fedora, je quitte le Cnam pour arriver dans l'agence Le Phare. Deux années après mon arrivée comme développeur, je suis propulsé Tech Lead, en charge de la refonte des outils de production. Je préconise le framework Symfony dont la version 2.0 venait juste de sortir.</p>

    <p class="mb-6">À l'heure où j'écris ces lignes, je viens de fêter mes dix ans au Phare. J'ai conçu et développé une bonne dizaine d'applications dans des secteurs variés et je suis maintenant directeur technique. Ma mission principale est de résoudre les problèmes techniques qui se mettent en travers de la route de la trentaine de personnes qui composent l'agence Le Phare.</p>

    <p class="mb-6">Depuis 2018, je suis papa d'un petit garçon et j'ai aujourd'hui envie de passer plus de temps avec ma famille. J'opère donc durant l'année 2020 un tournant dans ma carrière en quittant Le Phare pour devenir indépendant.</p>

    <p class="mb-6">J'ai hâte de connaitre la suite :-)</p>

@endsection
