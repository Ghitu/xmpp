<?php

/**
 * Copyright 2014 Fabian Grutschus. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies,
 * either expressed or implied, of the copyright holders.
 *
 * @author    Fabian Grutschus <f.grutschus@lubyte.de>
 * @copyright 2014 Fabian Grutschus. All rights reserved.
 * @license   BSD
 * @link      http://github.com/fabiang/xmpp
 */

namespace Updivision\Xmpp\EventListener\Stream;

use Updivision\Xmpp\Event\XMLEvent;
use Updivision\Xmpp\EventListener\AbstractEventListener;
use Updivision\Xmpp\EventListener\BlockingEventListenerInterface;

/**
 * Listener
 *
 * @package Xmpp\EventListener
 */
class Stream extends AbstractEventListener implements BlockingEventListenerInterface
{

    /**
     * Listener blocks stream.
     *
     * @var boolean
     */
    protected $blocking = false;

    /**
     * {@inheritDoc}
     */
    public function attachEvents()
    {
        $this->getOutputEventManager()
            ->attach('{http://etherx.jabber.org/streams}stream', array($this, 'streamStart'));

        $input = $this->getInputEventManager();
        $input->attach('{http://etherx.jabber.org/streams}stream', array($this, 'streamServer'));
        $input->attach('{http://etherx.jabber.org/streams}features', array($this, 'features'));
    }

    /**
     * Stream starts.
     *
     * @param XMLEvent $event XMLEvent
     * @return void
     */
    public function streamStart(XMLEvent $event)
    {
        if (true === $event->isStartTag()) {
            $this->blocking = true;
        }
    }

    /**
     * Stream server.
     *
     * @param XMLEvent $event XMLEvent
     * @return void
     */
    public function streamServer(XMLEvent $event)
    {
        if (false === $event->isStartTag()) {
            $this->blocking = false;

            if ($this->getConnection()->isConnected()) {
                $this->getConnection()->disconnect();
            }
        }
    }

    /**
     * Server send stream start.
     *
     * @return void
     */
    public function features()
    {
        $this->blocking = false;
        $this->getConnection()->setReady(true);
    }

    /**
     * {@inheritDoc}
     */
    public function isBlocking()
    {
        return $this->blocking;
    }
}
