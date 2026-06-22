<?php

namespace App\Mail;

use App\Models\Contact;
use App\Models\InboundMessage;
use BackedEnum;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;

class InboundMessageNotificationMail extends Mailable
{
    public function __construct(
        public readonly InboundMessage $inboundMessage,
        public readonly ?string $recipientSource = null,
    ) {}

    public function build(): static
    {
        $contact = $this->contact();
        $contactName = $this->contactName($contact);
        $sender = $this->sender();
        $channelLabel = $this->channelLabel();

        return $this
            ->subject('New inbound '.$channelLabel.' message from '.$this->subjectSender($contactName, $sender))
            ->view('email', [
                'subject' => 'New inbound message',
                'preheader' => 'A new inbound '.$channelLabel.' message was received.',
                'headline' => 'New inbound message',
                'body' => [
                    $contact
                        ? 'A contact replied through '.$channelLabel.'.'
                        : 'An inbound '.$channelLabel.' message was received, but no matching contact was found.',
                ],
                'details' => [
                    'Contact' => $contactName ?: 'No matched contact',
                    'Channel' => strtoupper($this->channelValue()),
                    'Sender' => $sender,
                    'Received' => $this->receivedAt(),
                    'Message' => $this->inboundMessage->body ?: '(No message body)',
                ],
                'cta' => $contact ? [
                    'label' => 'View CRM Contact',
                    'url' => route('crm.contacts.show', $contact),
                ] : null,
                'footer' => 'This is an internal notification from '.config('app.name').'.',
            ]);
    }

    private function contact(): ?Contact
    {
        $recipient = $this->inboundMessage->recipient;

        return $recipient instanceof Contact ? $recipient : null;
    }

    private function contactName(?Contact $contact): ?string
    {
        if (! $contact) {
            return null;
        }

        $name = trim((string) ($contact->name ?: trim(
            trim((string) $contact->first_name).' '.trim((string) $contact->last_name)
        )));

        return $name !== '' ? $name : $contact->email;
    }

    private function sender(): string
    {
        return $this->inboundMessage->from_value ?: 'Unknown sender';
    }

    private function channelLabel(): string
    {
        return Str::of($this->channelValue())
            ->lower()
            ->headline()
            ->toString();
    }

    private function channelValue(): string
    {
        $channel = $this->inboundMessage->channel;

        if ($channel instanceof BackedEnum) {
            return (string) $channel->value;
        }

        return (string) $channel;
    }

    private function subjectSender(?string $contactName, string $sender): string
    {
        return $contactName ?: $sender;
    }

    private function receivedAt(): string
    {
        return $this->inboundMessage->received_at
            ? $this->inboundMessage->received_at
                ->timezone(config('app.timezone'))
                ->format('M j, Y g:i A T')
            : 'Unknown';
    }
}