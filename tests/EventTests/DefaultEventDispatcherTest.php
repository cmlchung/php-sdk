<?php
/**
 * Copyright 2016, Optimizely
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Optimizely\Tests;

use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Message\EntityEnclosingRequest as HttpClientRequest;
use Optimizely\Event\Dispatcher\DefaultEventDispatcher;
use Optimizely\Event\LogEvent;

class DefaultEventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testDispatchEvent()
    {
        $logEvent = new LogEvent(
            'https://logx.optimizely.com',
            [
                'accountId' => '1234',
                'projectId' => '9876',
                'visitorId' => 'testUser'
            ],
            'POST',
            [
                'Content-Type' => 'application/json'
            ]
        );

        $expectedOptions = [
            'timeout' => 10,
            'connect_timeout' => 10
        ];

        $guzzleClientMock = $this->getMockBuilder(HttpClient::class)
            ->getMock();

        $guzzleClientRequestMock = $this->getMockBuilder(HttpClientRequest::class)
            ->setConstructorArgs([$logEvent->getHttpVerb(), $logEvent->getUrl(), $logEvent->getHeaders()])
            ->getMock();

        $guzzleClientMock->expects($this->once())
            ->method('post')
            ->with($logEvent->getUrl(), $logEvent->getHeaders(), $expectedOptions)
            ->willReturn($guzzleClientRequestMock);

        $guzzleClientRequestMock->expects($this->once())
            ->method('setBody')
            ->with(json_encode($logEvent->getParams()), 'application/json');

        $eventDispatcher = new DefaultEventDispatcher($guzzleClientMock);
        $eventDispatcher->dispatchEvent($logEvent);
    }
}
