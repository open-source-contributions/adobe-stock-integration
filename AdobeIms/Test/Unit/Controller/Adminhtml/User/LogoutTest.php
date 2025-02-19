<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Controller\Adminhtml\User;

use Magento\AdobeIms\Controller\Adminhtml\User\Logout;
use Magento\AdobeImsApi\Api\LogOutInterface;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Get logout test.
 */
class LogoutTest extends TestCase
{
    /**
     * @var MockObject|LogOutInterface $logoutInterfaceMock
     */
    private $logoutInterfaceMock;

    /**
     * @var MockObject|ActionContext $context
     */
    private $context;

    /**
     * @var Logout $getLogout
     */
    private $getLogout;

    /**
     * @var MockObject $request
     */
    private $request;

    /**
     * @var MockObject $resultFactory
     */
    private $resultFactory;

    /**
     * @var MockObject $jsonObject
     */
    private $jsonObject;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->logoutInterfaceMock = $this->createMock(LogOutInterface::class);
        $this->context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->resultFactory = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $this->context->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->jsonObject = $this->createMock(Json::class);
        $this->resultFactory->expects($this->once())->method('create')->with('json')->willReturn($this->jsonObject);

        $this->getLogout = new Logout(
            $this->context,
            $this->logoutInterfaceMock
        );
    }

    /**
     * Verify that user can be logout
     */
    public function testExecute()
    {
        $this->logoutInterfaceMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $data = ['success' => true];
        $this->jsonObject->expects($this->once())->method('setHttpResponseCode')->with(200);
        $this->jsonObject->expects($this->once())->method('setData')
            ->with($this->equalTo($data));
        $this->getLogout->execute();
    }

    /**
     * Verify that return will be false if there is an error in logout.
     * @throws NotFoundException
     */
    public function testExecuteWithError()
    {
        $result = [
            'success' => false,
        ];
        $this->logoutInterfaceMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);
        $this->jsonObject->expects($this->once())->method('setHttpResponseCode')->with(500);
        $this->jsonObject->expects($this->once())->method('setData')
            ->with($this->equalTo($result));
        $this->getLogout->execute();
    }
}
