<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Generated\Shared\Transfer;

use SprykerEngine\Shared\Transfer\AbstractTransfer;
use SprykerEngine\Shared\Transfer\Exception\RequiredTransferPropertyException;
use Generated\Shared\Test\CatFaceInterface as TestCatFaceInterface;
use Generated\Shared\Transfer\ItemTransfer;

/**
 * !!! THIS FILE IS AUTO-GENERATED, EVERY CHANGE WILL BE LOST WITH THE NEXT RUN OF TRANSFER GENERATOR
 * !!! DO NOT CHANGE ANYTHING IN THIS FILE
 */
class CatFaceTransfer extends AbstractTransfer implements TestCatFaceInterface
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ItemTransfer
     */
    protected $item;

    /**
     * @var \ArrayObject|ItemTransfer[]
     */
    protected $items;

    public function __construct()
    {
        $this->items = new \ArrayObject();
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->addModifiedProperty('name');

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @throws RequiredTransferPropertyException
     *
     * @return self
     */
    public function requireName()
    {
        $this->assertPropertyIsSet('name');

        return $this;
    }

    /**
     * @param ItemTransfer $item
     *
     * @return $this
     */
    public function setItem(ItemTransfer $item)
    {
        $this->item = $item;
        $this->addModifiedProperty('item');

        return $this;
    }

    /**
     * @return ItemTransfer
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @throws RequiredTransferPropertyException
     *
     * @return self
     */
    public function requireItem()
    {
        $this->assertPropertyIsSet('item');

        return $this;
    }

    /**
     * @param \ArrayObject|ItemTransfer[] $items
     *
     * @return $this
     */
    public function setItems(\ArrayObject $items)
    {
        $this->items = $items;
        $this->addModifiedProperty('items');

        return $this;
    }

    /**
     * @return ItemTransfer[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ItemTransfer $item
     *
     * @return $this
     */
    public function addItem(ItemTransfer $item)
    {
        $this->items[] = $item;
        $this->addModifiedProperty('items');

        return $this;
    }

    /**
     * @throws RequiredTransferPropertyException
     *
     * @return self
     */
    public function requireItems()
    {
        $this->assertCollectionPropertyIsSet('items');

        return $this;
    }

}
