<?php

declare(strict_types=1);

// Standalone unit regression test: php tests/handle_article.php
// Contao's static model lookups and routing are replaced with in-memory fixtures.
namespace Contao {
    class ContentText
    {
        public $article = 7;
        public $target = false;
    }

    class ArticleModel
    {
        public static ?self $published = null;
        public $id = 7;
        public $pid = 42;
        public $alias = 'selected-article';
        public $title = 'Selected article';

        public static function findPublishedById($id, array $options = []): ?self
        {
            return self::$published?->id == $id ? self::$published : null;
        }
    }

    class PageModel
    {
        public static array $pages = [];
        public $domain = 'example.org';
        public $rootId = 1;

        public function __construct(public int $id, private string $url)
        {
        }

        public static function findPublishedById($id): ?self
        {
            return self::$pages[$id] ?? null;
        }

        public static function findByPk($id): ?self
        {
            return self::$pages[$id] ?? null;
        }

        public function loadDetails(): self
        {
            return $this;
        }

        public function getFrontendUrl($parameters = null): string
        {
            // Distinct routes make selecting the wrong page observable.
            return $this->id === 1 ? '/' : $this->url . $parameters . '.html';
        }
    }

    class Config
    {
        public static bool $disableAlias = false;

        public static function get($key): bool
        {
            return $key === 'disableAlias' && self::$disableAlias;
        }
    }

    class Environment
    {
        public static function get($key): string
        {
            return 'example.org';
        }
    }

    class StringUtil
    {
        public static function ampersand($value): string
        {
            return str_replace('&', '&amp;', $value);
        }
    }
}

namespace {
    use Contao\ArticleModel;
    use Contao\Config;
    use Contao\PageModel;
    use HeimrichHannot\ContaoTeaserBundle\ContentElement\LinkTeaserElement;

    require __DIR__ . '/../src/ContentElement/LinkTeaserElement.php';

    function check($expected, $actual, string $message): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException($message . ': expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
    }

    $GLOBALS['TL_LANG']['MSC']['linkteaser']['articleTitle'] = 'Read %s';
    $cases = [
        ['article alias', 'selected-article', false, 'example.org', '/details', '/details/articles/selected-article.html', false],
        ['missing alias', '', false, 'example.org', '/details', '/details/articles/7.html', false],
        ['aliases disabled', 'selected-article', true, 'example.org', '/details', '/details/articles/7.html', false],
        ['different domain', 'selected-article', false, 'other.example', 'https://other.example/details', 'https://other.example/details/articles/selected-article.html', true],
        ['escaped URL', 'selected-article', false, 'example.org', '/details?a=1&b=2', '/details?a=1&amp;b=2/articles/selected-article.html', false],
    ];

    foreach ($cases as [$name, $alias, $disableAlias, $domain, $pageUrl, $expectedUrl, $expectedTarget]) {
        ArticleModel::$published = new ArticleModel();
        ArticleModel::$published->alias = $alias;
        Config::$disableAlias = $disableAlias;
        $page = new PageModel(42, $pageUrl);
        $page->domain = $domain;
        PageModel::$pages = [1 => new PageModel(1, '/'), 42 => $page];

        $element = new class extends LinkTeaserElement {
            public function handleArticle()
            {
                return parent::handleArticle();
            }
        };
        $element->setLink('More about %s');

        check(true, $element->handleArticle(), $name . ': link available');
        check($expectedUrl, $element->getHref(), $name . ': article URL');
        check('Read Selected article', $element->getTitle(), $name . ': title');
        check('More about Selected article', $element->getLink(), $name . ': label');
        check($expectedTarget, $element->target, $name . ': target');
    }

    ArticleModel::$published = null;
    check(false, $element->handleArticle(), 'missing article');
    ArticleModel::$published = new ArticleModel();
    PageModel::$pages = [];
    check(false, $element->handleArticle(), 'missing parent page');

    echo "PASS: 7 article link scenarios\n";
}
