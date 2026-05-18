<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use ZEDMagdy\FilamentChat\Livewire\MessageInput;
use ZEDMagdy\FilamentChat\Models\Conversation;
use ZEDMagdy\FilamentChat\Models\Message;
use ZEDMagdy\FilamentChat\Models\Participant;
use ZEDMagdy\FilamentChat\Tests\Fixtures\User;

/**
 * Builds a Livewire temporary uploaded file backed by a real fake file so the
 * full attachment-saving path (validation + media persistence) can run.
 */
function fakeTemporaryUpload(string $name = 'photo.jpg'): TemporaryUploadedFile
{
    $disk = FileUploadConfiguration::disk();
    Storage::fake($disk);

    $fileName = Str::uuid()->toString().'-'.$name;

    Storage::disk($disk)->put(
        'livewire-tmp/'.$fileName,
        UploadedFile::fake()->image($name)->get(),
    );

    return TemporaryUploadedFile::createFromLivewire($fileName);
}

function makeMessageInput(int $conversationId): MessageInput
{
    $component = new MessageInput;
    $component->conversationId = $conversationId;
    $component->mount();

    return $component;
}

beforeEach(function (): void {
    Storage::fake('public');

    Filament::setCurrentPanel(Filament::getPanel('testing'));

    $this->user = User::factory()->create();
    $this->conversation = Conversation::factory()->create();

    Participant::factory()->create([
        'conversation_id' => $this->conversation->id,
        'participantable_id' => $this->user->id,
        'participantable_type' => $this->user->getMorphClass(),
    ]);

    $this->actingAs($this->user);
});

it('sends a text-only message', function (): void {
    $component = makeMessageInput($this->conversation->id);
    $component->data['body'] = 'hello there';

    $component->sendMessage();

    $message = Message::query()->where('conversation_id', $this->conversation->id)->sole();

    expect($message->body)->toBe('hello there');
});

it('sends a message with an attachment and no text body', function (): void {
    $component = makeMessageInput($this->conversation->id);
    $component->showAttachments = true;
    $component->data['attachments'] = [Str::uuid()->toString() => fakeTemporaryUpload()];

    $component->sendMessage();

    $message = Message::query()->where('conversation_id', $this->conversation->id)->first();

    expect($message)->not->toBeNull()
        ->and($message->body)->toBeNull()
        ->and($message->getMedia(config('filament-chat.attachments.collection'))->count())->toBe(1);
});

it('does nothing when both body and attachments are empty', function (): void {
    $component = makeMessageInput($this->conversation->id);

    $component->sendMessage();

    expect(Message::query()->where('conversation_id', $this->conversation->id)->count())->toBe(0);
});
