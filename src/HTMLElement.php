<?php
namespace PatrykNamyslak\Builders;

use InvalidArgumentException;


class HtmlElement{

    /**
     * @var string[] An array of attributes an element should have at the time of rendering, `example:` `["class" => "btn-primary"]`
     */
    public array $attributes = [];
    public string $contents = "";
    public bool $delayEndTag = false;

    /**
     * @param string $tag HTML tag name
     */
    public function __construct(public string $tag = "div"){
        $t = &$this->tag;
        $t = ltrim($t, "<");
        $t = rtrim($t, ">");
    }

    public function prependContents(string $contents): static{
        $this->contents = $contents . $this->contents;
        return $this;
    }
    public function contents(string $contents): static{
        $this->contents = $contents;
        return $this;
    }
    public function appendContents(string $contents): static{
        $this->contents .= $contents;
        return $this;
    }


    /**
     * `Limited` version of `HtmlElement::attributes()` method, limits to only `one` attribute at a time. `Ideal for chaining`!
     * @param array $attribute
     * @throws InvalidArgumentException
     * @return never
     */
    public function attribute(array $attribute): static{
        if (count($attribute) > 1){
            throw new InvalidArgumentException('$attribute cannot contain more than one attribute, please call ' . self::class . "::attributes() to set multiple attributes");
        }
        $this->attributes($attribute);
        return $this;
    }
    /**
     * @param string[] $attributes Expects an associative array, this method will `OVERWRITE` any existing attributes.
     * @return HtmlElement
     */
    public function attributes(array $attributes): static{
        foreach($attributes as $attributeName => $attributeValue){
            $this->attributes[$attributeName] = $attributeValue;
        }
        return $this;
    }


    public function needsEndTag(): bool{
        return match ($this->tag){
            "img","area","base","br","col","embed","hr","input","link","meta","param","source","track","wbr","video","audio" => false,
            default => true,
        };
    }

    protected function attributesToString(): string{
        return implode(" ", array_values(array_map(function($attributeValue, $attributeName): string{
            return $attributeName . '="' . $attributeValue . '"';
        }, $this->attributes, array_keys($this->attributes))));
    }

    /**
     * Render the element
     * @param bool $delayEndTag Omit the end tag, then run `HtmlElement::endTag()`;
     * @return string
     */
    public function render(bool $delayEndTag = false): string{
        $this->delayEndTag = $delayEndTag;
        $element = "<{$this->tag} {$this->attributesToString()}>";
        if (!$delayEndTag and $this->needsEndTag()){
            $element .= $this->contents;
            $element .= "</{$this->tag}>";
        }
        return $element;
    }

    public function renderEndTag(): ?string{
        if ($this->delayEndTag){
            return "</{$this->tag}>";
        }
        return null;
    }
}