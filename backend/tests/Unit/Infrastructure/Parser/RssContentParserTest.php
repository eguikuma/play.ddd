<?php

namespace Tests\Unit\Infrastructure\Parser;

use App\Infrastructure\Parser\RssContentParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RssContentParserTest extends TestCase
{
    private RssContentParser $parser;

    protected function setUp(): void
    {
        $this->parser = new RssContentParser;
    }

    #[Test]
    public function RSS20フィードをパースできる(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Test Feed</title>
    <link>https://example.com</link>
    <item>
      <title>Article 1</title>
      <link>https://example.com/article-1</link>
      <description>Description of article 1</description>
      <pubDate>Fri, 10 Apr 2026 12:00:00 +0000</pubDate>
    </item>
    <item>
      <title>Article 2</title>
      <link>https://example.com/article-2</link>
      <description>Description of article 2</description>
    </item>
  </channel>
</rss>
XML;

        $entries = $this->parser->parse($xml);

        $this->assertCount(2, $entries);
        $this->assertSame('Article 1', $entries[0]->title);
        $this->assertSame('https://example.com/article-1', $entries[0]->url);
        $this->assertSame('Description of article 1', $entries[0]->body);
        $this->assertNotNull($entries[0]->publishedAt);
    }

    #[Test]
    public function Atomフィードをパースできる(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <title>Test Atom Feed</title>
  <entry>
    <title>Atom Article</title>
    <link href="https://example.com/atom-article"/>
    <content type="text">Atom content here</content>
    <updated>2026-04-10T12:00:00Z</updated>
  </entry>
</feed>
XML;

        $entries = $this->parser->parse($xml);

        $this->assertCount(1, $entries);
        $this->assertSame('Atom Article', $entries[0]->title);
        $this->assertSame('https://example.com/atom-article', $entries[0]->url);
    }

    #[Test]
    public function リンクのないエントリはスキップされる(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Test Feed</title>
    <item>
      <title>No Link Article</title>
      <description>This entry has no link</description>
    </item>
    <item>
      <title>With Link</title>
      <link>https://example.com/with-link</link>
      <description>This entry has a link</description>
    </item>
  </channel>
</rss>
XML;

        $entries = $this->parser->parse($xml);

        $this->assertCount(1, $entries);
        $this->assertSame('With Link', $entries[0]->title);
    }

    #[Test]
    public function HTMLタグは除去される(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Test Feed</title>
    <item>
      <title>HTML Article</title>
      <link>https://example.com/html</link>
      <description>&lt;p&gt;Bold &lt;b&gt;text&lt;/b&gt;&lt;/p&gt;</description>
    </item>
  </channel>
</rss>
XML;

        $entries = $this->parser->parse($xml);

        $this->assertSame('Bold text', $entries[0]->body);
    }
}
