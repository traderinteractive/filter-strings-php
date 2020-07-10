<?php

namespace Filter;

use PHPUnit\Framework\TestCase;
use TraderInteractive\Exceptions\FilterException;
use TraderInteractive\Filter\XmlFilter;

/**
 * @coversDefaultClass \TraderInteractive\Filter\XmlFilter
 * @covers ::<private>
 */
final class XmlFilterTest extends TestCase
{
    /**
     * @var string
     */
    const FULL_XML = (''
        . "<?xml version=\"1.0\"?>\n"
        . '<books>'
            . '<book id="bk101">'
                . '<author>Gambardella, Matthew</author>'
                . "<title>XML Developer's Guide</title>"
                . '<genre>Computer</genre>'
                . '<price>44.95</price>'
                . '<publish_date>2000-10-01</publish_date>'
                . '<description>An in-depth look at creating applications with XML.</description>'
            . '</book>'
            . '<book id="bk102">'
                . '<author>Ralls, Kim</author>'
                . '<title>Midnight Rain</title>'
                . '<genre>Fantasy</genre>'
                . '<price>5.95</price>'
                . '<publish_date>2000-12-16</publish_date>'
                . '<description>A former architect battles corporate zombies</description>'
            . '</book>'
        . "</books>\n"
    );

    /**
     * @test
     * @covers ::extract
     */
    public function extract()
    {
        $xpath = "/books/book[@id='bk101']";
        $actualXml = XmlFilter::extract(self::FULL_XML, $xpath);
        $expectedXml = (''
            . '<book id="bk101">'
            . '<author>Gambardella, Matthew</author>'
            . "<title>XML Developer's Guide</title>"
            . '<genre>Computer</genre>'
            . '<price>44.95</price>'
            . '<publish_date>2000-10-01</publish_date>'
            . '<description>An in-depth look at creating applications with XML.</description>'
            . '</book>'
        );

        $this->assertSame($expectedXml, $actualXml);
    }

    /**
     * @test
     * @covers ::extract
     */
    public function extractNonXmlValue()
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage('String could not be parsed as XML');
        $notXml = json_encode(['foo' => 'bar']);
        $xpath = '/books/book';
        XmlFilter::extract($notXml, $xpath);
    }

    /**
     * @test
     * @covers ::extract
     */
    public function extractNoElementFound()
    {
        $xpath = '/catalog/books/book';
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage(sprintf(XmlFilter::EXTRACT_NO_ELEMENT_FOUND_ERROR_FORMAT, $xpath));
        XmlFilter::extract(self::FULL_XML, $xpath);
    }

    /**
     * @test
     * @covers ::extract
     */
    public function extractMultipleElementFound()
    {
        $xpath = '/books/book';
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage(sprintf(XmlFilter::EXTRACT_MULTIPLE_ELEMENTS_FOUND_ERROR_FORMAT, $xpath));
        XmlFilter::extract(self::FULL_XML, $xpath);
    }

    /**
     * @test
     * @covers ::validate
     */
    public function validate()
    {
        $xml = self::FULL_XML;
        $xsdFile = __DIR__ . '/_files/books.xsd';
        $validatedXml = XmlFilter::validate($xml, $xsdFile);
        $this->assertSame($xml, $validatedXml);
    }

    /**
     * @test
     * @covers ::validate
     */
    public function validateXmlMissingRequiredAttribute()
    {
        $xmlMissingId = (''
            . '<books>'
                . '<book>'
                    . '<author>Gambardella, Matthew</author>'
                    . "<title>XML Developer's Guide</title>"
                    . '<genre>Computer</genre>'
                    . '<price>44.95</price>'
                    . '<publish_date>2000-10-01</publish_date>'
                    . '<description>An in-depth look at creating applications with XML.</description>'
                . '</book>'
            . '</books>'
        );

        $this->expectException(FilterException::class);
        $this->expectExceptionMessage("Element 'book': The attribute 'id' is required but missing");
        $xsdFile = __DIR__ . '/_files/books.xsd';
        XmlFilter::validate($xmlMissingId, $xsdFile);
    }

    /**
     * @test
     * @covers ::validate
     */
    public function validateEmptyXml()
    {
        $emptyXml = '';
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage('String could not be parsed as XML');
        $xsdFile = __DIR__ . '/_files/books.xsd';
        XmlFilter::validate($emptyXml, $xsdFile);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filter()
    {
        $filteredXml = XmlFilter::filter(self::FULL_XML);
        $this->assertSame(self::FULL_XML, $filteredXml);
    }
}
