<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Struct;

abstract class Collection extends Struct implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $elements = [];

    public function __construct(iterable $elements = [])
    {
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    public function add($element): void
    {
        $this->validateType($element);

        $this->elements[] = $element;
    }

    public function set($key, $element): void
    {
        $this->validateType($element);

        if ($key === null) {
            $this->elements[] = $element;
        } else {
            $this->elements[$key] = $element;
        }
    }

    /**
     * @param mixed|null $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->elements[$key];
        }

        return null;
    }

    public function clear(): void
    {
        $this->elements = [];
    }

    public function count(): int
    {
        return \count($this->elements);
    }

    public function getKeys(): array
    {
        return array_keys($this->elements);
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->elements);
    }

    public function map(\Closure $closure): array
    {
        return array_map($closure, $this->elements);
    }

    public function reduce(\Closure $closure, $initial = null): array
    {
        return array_reduce($this->elements, $closure, $initial);
    }

    public function fmap(\Closure $closure): array
    {
        return array_filter($this->map($closure));
    }

    public function sort(\Closure $closure)
    {
        uasort($this->elements, $closure);
    }

    /**
     * @return static
     */
    public function filterInstance(string $class)
    {
        return $this->filter(function ($item) use ($class) {
            return $item instanceof $class;
        });
    }

    /**
     * @return static
     */
    public function filter(\Closure $closure)
    {
        return new static(array_filter($this->elements, $closure));
    }

    /**
     * @return static
     */
    public function slice(int $offset, ?int $length = null)
    {
        return new static(\array_slice($this->elements, $offset, $length, true));
    }

    public function getElements(): array
    {
        return $this->elements;
    }

    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);
        $data['elements'] = array_values($this->elements);
        $data['_class'] = \get_class($this);

        return $data;
    }

    public function first()
    {
        return array_values($this->elements)[0] ?? null;
    }

    public function last()
    {
        return array_values($this->elements)[\count($this->elements) - 1] ?? null;
    }

    public function remove($key): void
    {
        unset($this->elements[$key]);
    }

    public function getIterator(): \Generator
    {
        yield from $this->elements;
    }

    protected function getExpectedClass(): ?string
    {
        return null;
    }

    protected function validateType($element): void
    {
        $expectedClass = $this->getExpectedClass();
        if ($expectedClass === null) {
            return;
        }

        $elementClass = get_class($element);

        if (!$element instanceof $expectedClass) {
            throw new \InvalidArgumentException(
                sprintf('Expected collection element of type %s got %s', $expectedClass, $elementClass)
            );
        }
    }
}
