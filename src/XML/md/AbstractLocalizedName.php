<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\StringElementTrait;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;

use function array_key_first;

/**
 * Abstract class implementing LocalizedNameType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractLocalizedName extends AbstractMdElement implements ArrayizableElementInterface
{
    use StringElementTrait;


    /**
     * LocalizedNameType constructor.
     *
     * @param string $language The language this string is localized in.
     * @param string $value The localized string.
     */
    final public function __construct(
        protected string $language,
        string $value,
    ) {
        Assert::notWhitespaceOnly($language, ProtocolViolationException::class);

        $this->setContent($value);
    }


    /**
     * Get the language this string is localized in.
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }


    /**
     * Create an instance of this object from its XML representation.
     *
     * @param \DOMElement $xml
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, static::getLocalName(), InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, static::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C::NS_XML, 'lang'),
            'Missing xml:lang from ' . static::getLocalName(),
            MissingAttributeException::class,
        );

        return new static($xml->getAttributeNS(C::NS_XML, 'lang'), $xml->textContent);
    }


    /**
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    final public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttributeNS(C::NS_XML, 'xml:lang', $this->getLanguage());
        $e->textContent = $this->getContent();

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::count($data, 1, ArrayValidationException::class);

        $lang = array_key_first($data);
        Assert::stringNotEmpty($lang, ArrayValidationException::class);

        $value = $data[$lang];
        Assert::stringNotEmpty($value, ArrayValidationException::class);

        return new static($lang, $value);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        return [$this->language => $this->getContent()];
    }
}
