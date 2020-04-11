<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

return [
    'baseUrl' => '',
    'production' => false,
    'siteName' => 'Erwan Richard',
    'siteDescription' => 'Blog',
    'siteAuthor' => 'Erwan',

    // collections
    'collections' => [
        'posts' => [
            'author' => 'Erwan', // Default author, if not provided in a post
            'sort' => '-date',
            'path' => 'blog/{filename}',
        ],
    ],

    // helpers
    'getDate' => function ($page) {
        return Carbon::createFromFormat('U', $page->date, 'Europe/Paris')->locale('fr_FR');
    },
    'urlize' => function ($page, $category) {
        return strtr($category, 'é', 'e');
    },
    'getCommentUrl' => function ($page) {
        if (null === $page->ghcommentid) {
            return null;
        }

        return 'https://github.com/erichard/blog/issues/'.$page->ghcommentid;
    },
    'getComments' => function ($page) {
        if (null === $page->ghcommentid) {
            return null;
        }

        $cacheFile = 'cachegh/comments-'.$page->ghcommentid.'.json';

        if (!is_file($cacheFile)) {
            $url = 'https://api.github.com/repos/erichard/blog/issues/'.$page->ghcommentid.'/comments';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/vnd.github.v3.html+json', 'User-Agent: curl']);
            $result = curl_exec($ch);
            file_put_contents($cacheFile, $result);
            $comments = json_decode($result);
        } else {
            $comments = json_decode(file_get_contents($cacheFile));
        }

        return $comments;
    },
    'getCommentDate' => function ($page, $comment) {
        $date = new Carbon($comment->created_at);
        $date->locale('fr_FR');

        return $date;
    },
    'getExcerpt' => function ($page, $length = 255) {
        if ($page->excerpt) {
            return $page->excerpt;
        }

        $content = preg_split('/<!-- more -->/m', $page->getContent(), 2);
        $cleaned = trim(
            strip_tags(
                preg_replace(['/<pre>[\w\W]*?<\/pre>/', '/<h\d>[\w\W]*?<\/h\d>/'], '', str_replace('\n', '', $content[0])),
                '<code>'
            )
        );

        if (count($content) > 1) {
            return $cleaned;
        }

        $truncated = substr($cleaned, 0, $length);

        if (substr_count($truncated, '<code>') > substr_count($truncated, '</code>')) {
            $truncated .= '</code>';
        }

        return strlen($cleaned) > $length
            ? preg_replace('/\s+?(\S+)?$/', '', $truncated).'...'
            : $cleaned;
    },
    'isActive' => function ($page, $path) {
        return Str::endsWith(trimPath($page->getPath()), trimPath($path));
    },
];
