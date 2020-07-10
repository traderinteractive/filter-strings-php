<?php

namespace TraderInteractive\Filter;

use LibXMLError;
use SimpleXMLElement;
use Throwable;
use TraderInteractive\Exceptions\FilterException;

final class XmlFilter
{
    /**
     * @var string
     */
    const LIBXML_ERROR_FORMAT = '%s on line %d at column %d';

    /**
     * @var string
     */
    const EXTRACT_NO_ELEMENT_FOUND_ERROR_FORMAT = "No element found at xpath '%s'";

    /**
     * @var string
     */
    const EXTRACT_MULTIPLE_ELEMENTS_FOUND_ERROR_FORMAT = "Multiple elements found at xpath '%s'";

    /**
     * @param string $xml            The value to be filtered.
     * @param string $schemaFilePath The full path to the XSD file used for validation.
     *
     * @return string
     *
     * @throws FilterException Thrown if the given value cannot be filtered.
     */
    public static function validate(string $xml, string $schemaFilePath) : string
    {
        $previousLibxmlUserInternalErrors = libxml_use_internal_errors(true);
        try {
            libxml_clear_errors();

            $document = dom_import_simplexml(self::toSimpleXmlElement($xml))->ownerDocument;
            if ($document->schemaValidate($schemaFilePath)) {
                return $xml;
            }

            $formattedXmlError = self::formatXmlError(libxml_get_last_error());
            throw new FilterException($formattedXmlError);
        } finally {
            libxml_use_internal_errors($previousLibxmlUserInternalErrors);
        }
    } //@codeCoverageIgnore

    /**
     * @param string $xml   The value to be filtered.
     * @param string $xpath The xpath to the element to be extracted.
     *
     * @return string
     *
     * @throws FilterException Thrown if the value cannot be filtered.
     */
    public static function extract(string $xml, string $xpath) : string
    {
        $simpleXmlElement = self::toSimpleXmlElement($xml);
        $elements = $simpleXmlElement->xpath($xpath);

        $elementCount = count($elements);

        if ($elementCount === 0) {
            throw new FilterException(sprintf(self::EXTRACT_NO_ELEMENT_FOUND_ERROR_FORMAT, $xpath));
        }

        if ($elementCount > 1) {
            throw new FilterException(sprintf(self::EXTRACT_MULTIPLE_ELEMENTS_FOUND_ERROR_FORMAT, $xpath));
        }

        return $elements[0]->asXML();
    }

    /**
     * @param string $xml The value to be filtered.
     *
     * @return string
     *
     * @throws FilterException Thrown if the given string cannot be parsed as xml.
     */
    public static function filter(string $xml) : string
    {
        return self::toSimpleXmlElement($xml)->asXML();
    }

    private static function toSimpleXmlElement(string $xml) : SimpleXMLElement
    {
        try {
            return new SimpleXMLElement($xml);
        } catch (Throwable $throwable) {
            throw new FilterException($throwable->getMessage());
        }
    }

    private static function formatXmlError(LibXMLError $error) : string
    {
        $message = trim($error->message);
        return sprintf(self::LIBXML_ERROR_FORMAT, $message, $error->line, $error->column);
    }
}
