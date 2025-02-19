<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\FlushUserTokens;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * User flush token test.
 */
class FlushUserTokensTest extends TestCase
{

    /**
     * @var UserProfileRepositoryInterface|MockObject $userProfileRepository
     */
    private $userProfileRepository;

    /**
     * @var MockObject|UserContextInterface $userContext
     */
    private $userContext;

    /**
     * @var FlushUserTokens $flushTokens
     */
    private $flushTokens;

    /**
     * Prepare test objects.
     */
    public function setUp(): void
    {
        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->userProfileRepository = $this->createMock(UserProfileRepositoryInterface::class);

        $this->flushTokens = new FlushUserTokens(
            $this->userContext,
            $this->userProfileRepository
        );
    }

    /**
     * Test flush tokens
     */
    public function testExecute(): void
    {
        $this->userContext->expects($this->once())->method('getUserId')->willReturn(1);
        $userProfileMock = $this->createMock(\Magento\AdobeImsApi\Api\Data\UserProfileInterface::class);
        $this->userProfileRepository->expects($this->exactly(1))
            ->method('getByUserId')
            ->willReturn($userProfileMock);

        $userProfileMock->expects($this->once())->method('setAccessToken')->willReturnSelf();
        $userProfileMock->expects($this->once())->method('setRefreshToken')->willReturnSelf();
        $this->userProfileRepository->expects($this->once())->method('save')->with($userProfileMock)->willReturnSelf();
        $this->flushTokens->execute();
    }
}
