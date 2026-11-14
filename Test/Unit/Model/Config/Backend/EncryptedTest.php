<?php

namespace Vendor\OmniChat\Test\Unit\Model\Config\Backend;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vendor\OmniChat\Model\Config\Backend\Encrypted;

class EncryptedTest extends TestCase
{
    /**
     * @var EncryptorInterface|MockObject
     */
    private $encryptor;

    protected function setUp(): void
    {
        $this->encryptor = $this->createMock(EncryptorInterface::class);
    }

    public function testBeforeSaveKeepsStoredValueWhenMaskIsPosted(): void
    {
        $this->encryptor->method('decrypt')->with('0:3:stored-cipher')->willReturn('real-secret-abcd');
        $this->encryptor->expects($this->never())->method('encrypt');

        $model = $this->createBackend('0:3:stored-cipher');
        $model->setValue('************abcd');
        $model->beforeSave();

        $this->assertSame('0:3:stored-cipher', $model->getValue());
    }

    public function testBeforeSaveEncryptsChangedValue(): void
    {
        $this->encryptor->expects($this->once())
            ->method('encrypt')
            ->with('new-secret')
            ->willReturn('0:3:new-cipher');
        $this->encryptor->method('decrypt')->willReturn('real-secret-abcd');

        $model = $this->createBackend('0:3:stored-cipher');
        $model->setValue('new-secret');
        $model->beforeSave();

        $this->assertSame('0:3:new-cipher', $model->getValue());
    }

    public function testBeforeSaveDoesNotTouchEmptyValue(): void
    {
        $this->encryptor->expects($this->never())->method('encrypt');

        $model = $this->createBackend('0:3:stored-cipher');
        $model->setValue('');
        $model->beforeSave();

        $this->assertSame('', $model->getValue());
    }

    public function testBeforeSaveEncryptsFirstValue(): void
    {
        $this->encryptor->expects($this->once())
            ->method('encrypt')
            ->with('first-secret')
            ->willReturn('0:3:first-cipher');

        $model = $this->createBackend('');
        $model->setValue('first-secret');
        $model->beforeSave();

        $this->assertSame('0:3:first-cipher', $model->getValue());
    }

    private function createBackend(string $oldValue): Encrypted
    {
        $model = $this->getMockBuilder(Encrypted::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOldValue'])
            ->getMock();
        $model->method('getOldValue')->willReturn($oldValue);

        $property = new \ReflectionProperty(Encrypted::class, 'encryptor');
        $property->setValue($model, $this->encryptor);

        $eventManager = new \ReflectionProperty(AbstractModel::class, '_eventManager');
        $eventManager->setValue($model, $this->createStub(ManagerInterface::class));

        return $model;
    }
}
