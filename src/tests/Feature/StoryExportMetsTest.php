<?php

namespace Tests\Feature\Export;

use Database\Seeders\ItemDataSeeder;
use Database\Seeders\ItemPropertyDataSeeder;
use Database\Seeders\LanguageDataSeeder;
use Database\Seeders\ProjectDataSeeder;
use Database\Seeders\PropertyDataSeeder;
use Database\Seeders\PropertyTypeDataSeeder;
use Database\Seeders\StoryDataSeeder;
use Database\Seeders\TranscriptionDataSeeder;
use Database\Seeders\TranscriptionLanguageDataSeeder;
use Illuminate\Support\Facades\Artisan;
use DOMDocument;
use DOMXPath;
use Tests\TestCase;

class StoryExportMetsTest extends TestCase
{
    private const STORY_ID   = 1;
    private const ENDPOINT   = '/stories/' . self::STORY_ID . '/items/export/mets';

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => ProjectDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => StoryDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => LanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => TranscriptionLanguageDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => PropertyTypeDataSeeder::class]);
        Artisan::call('db:seed', ['--class' => ItemPropertyDataSeeder::class]);
    }

    public function test_export_returns_http_ok(): void
    {
        $this->get(self::ENDPOINT)->assertOk();
    }

    public function test_export_returns_application_xml_content_type(): void
    {
        $this->get(self::ENDPOINT)
            ->assertHeader('Content-Type', 'application/xml; charset=utf-8');
    }

    public function test_export_content_disposition_contains_story_id_and_xml_extension(): void
    {
        $disposition = $this->get(self::ENDPOINT)
            ->headers->get('Content-Disposition');

        $this->assertStringContainsString('filename=transcribathon_story-' . self::STORY_ID . '_', $disposition);
        $this->assertStringContainsString('.xml', $disposition);
    }

    public function test_export_returns_parseable_xml(): void
    {
        $content = $this->get(self::ENDPOINT)->streamedContent();
        $dom     = new DOMDocument();

        $this->assertTrue($dom->loadXML($content));
    }

    public function test_mets_root_element_is_present(): void
    {
        $xpath = $this->buildXPath($this->get(self::ENDPOINT)->streamedContent());

        $this->assertTrue($xpath->evaluate('boolean(/mets:mets)'));
    }

    public function test_mets_root_has_objid_matching_story_id(): void
    {
        $xpath = $this->buildXPath($this->get(self::ENDPOINT)->streamedContent());

        $this->assertSame((string) self::STORY_ID, $xpath->evaluate('string(/mets:mets/@OBJID)'));
    }

    public function test_mets_contains_mets_hdr(): void
    {
        $xpath = $this->buildXPath($this->get(self::ENDPOINT)->streamedContent());

        $this->assertSame(1, $xpath->query('/mets:mets/mets:metsHdr')->length);
    }

    public function test_mets_contains_md_sec(): void
    {
        $xpath = $this->buildXPath($this->get(self::ENDPOINT)->streamedContent());

        $this->assertSame(1, $xpath->query('/mets:mets/mets:mdSec')->length);
    }

    public function test_mets_contains_struct_sec(): void
    {
        $xpath = $this->buildXPath($this->get(self::ENDPOINT)->streamedContent());

        $this->assertSame(1, $xpath->query('/mets:mets/mets:structSec')->length);
    }

    public function test_uses_item_manifest_when_story_has_none(): void
    {
        $xpath  = $this->buildXPath($this->get(self::ENDPOINT)->streamedContent());
        $locref = $xpath->evaluate('string(/mets:mets/mets:mdSec/mets:md/mets:mdRef/@LOCREF)');

        $this->assertSame('http://example.com/manifest1fromItem.json', $locref);
    }

    public function test_file_sec_contains_at_least_one_item_alto_xml(): void
    {
        $xpath = $this->buildXPath($this->get(self::ENDPOINT)->streamedContent());

        $altoFiles = $xpath->query('//mets:fileGrp[@USE="ALTO"]/mets:file');

        $this->assertGreaterThan(0, $altoFiles->length);
    }

    public function test_struct_map_item_divs_match_seeded_item_count(): void
    {
        $xpath     = $this->buildXPath($this->get(self::ENDPOINT)->streamedContent());
        $itemDivs  = $xpath->query('//mets:structMap/mets:div/mets:div[@TYPE="item"]');
        $altoFiles = $xpath->query('//mets:fileGrp[@USE="ALTO"]/mets:file');

        // number of item divs must equal number of ALTO files
        $this->assertSame($altoFiles->length, $itemDivs->length);
    }

    public function test_struct_map_fptr_fileids_resolve_to_fileSec_entries(): void
    {
        $xml  = $this->get(self::ENDPOINT)->streamedContent();
        $dom  = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = $this->buildXPath($xml);

        // collect all file IDs declared in fileSec
        $fileNodes = $xpath->query('//mets:fileSec//mets:file/@ID');
        $declaredIds = [];
        foreach ($fileNodes as $node) {
            $declaredIds[] = $node->value;
        }

        // collect all FILEIDs referenced in structMap
        $fptrNodes = $xpath->query('//mets:structMap//mets:fptr/@FILEID');
        foreach ($fptrNodes as $node) {
            $this->assertContains(
                $node->value,
                $declaredIds,
                "structMap fptr FILEID '{$node->value}' has no matching file in fileSec"
            );
        }
    }

    public function test_unknown_story_returns_404(): void
    {
        $this->get('/stories/999999/items/export/mets')->assertNotFound();
    }

    private function buildXPath(string $xml): DOMXPath
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('mets',    'http://www.loc.gov/METS/v2');
        $xpath->registerNamespace('dc',      'http://purl.org/dc/elements/1.1/');
        $xpath->registerNamespace('dcterms', 'http://purl.org/dc/terms/');
        $xpath->registerNamespace('edm',     'http://www.europeana.eu/schemas/edm/');
        $xpath->registerNamespace('premis',  'http://www.loc.gov/premis/v3');
        $xpath->registerNamespace('alto',    'http://www.loc.gov/standards/alto/ns-v4#');

        return $xpath;
    }
}
