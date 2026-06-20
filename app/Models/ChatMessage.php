<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use SoftDeletes;

    protected $table = 'chat_messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'type',
        'body',
        'file_path',
        'file_name',
        'file_mime',
        'file_size',
        'reply_to_id',
        'delivered_at',
        'deleted_by',
    ];

    protected $casts = [
        'file_size'    => 'integer',
        'created_at'   => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function sender()
    {
        return $this->belongsTo(AlumniUser::class, 'sender_id');
    }

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_id');
    }

    public function reads()
    {
        return $this->hasMany(ChatMessageRead::class, 'message_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isDeleted(): bool { return $this->trashed(); }
    public function isSystem(): bool  { return $this->type === 'system'; }
    public function isMedia(): bool   { return in_array($this->type, ['image', 'video', 'file', 'pdf']); }

    public function fileUrl(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function fileSizeHuman(): string
    {
        if (!$this->file_size) return '';
        $kb = $this->file_size / 1024;
        if ($kb < 1024) return round($kb, 1) . ' KB';
        return round($kb / 1024, 1) . ' MB';
    }

    public function preview(): string
    {
        if ($this->isDeleted()) return 'Message deleted';
        if ($this->isSystem())  return $this->body ?? '';
        return match ($this->type) {
            'image' => '📷 Photo',
            'video' => '🎬 Video',
            'pdf'   => '📄 ' . ($this->file_name ?? 'PDF'),
            'file'  => '📎 ' . ($this->file_name ?? 'File'),
            default => $this->body ?? '',
        };
    }

    /**
     * Tick state for the sender's UI:
     *
     * 'sent'      — saved to DB, recipient not yet online
     * 'delivered' — recipient has been online (delivered_at set)
     * 'read'      — recipient opened the conversation (ChatMessageRead exists)
     *
     * For GROUP messages: 'read' = at least one other member has read it.
     */
    public function tickState(int $senderId): array
    {
        if ((int) $this->sender_id !== $senderId) {
            return ['state' => '', 'read_at' => null];
        }

        $readRow = ChatMessageRead::where('conversation_id', $this->conversation_id)
            ->where('alumni_id', '!=', $senderId)
            ->where('last_read_message_id', '>=', $this->id)
            ->select('updated_at')
            ->first();

        if ($readRow) {
            return ['state' => 'read', 'read_at' => $readRow->updated_at];
        }

        if ($this->delivered_at) {
            return ['state' => 'delivered', 'read_at' => null];
        }

        return ['state' => 'sent', 'read_at' => null];
    }

    /**
     * Serialize for API responses.
     */
    public function toApiArray(int $myId): array
    {
        $sender    = $this->sender;
        $isMine = (int) $this->sender_id === $myId;
        $tick   = $isMine ? $this->tickState($myId) : ['state' => '', 'read_at' => null];

        return [
            'id'              => $this->id,
            'type'            => $this->type,
            'body'            => $this->isDeleted() ? null : $this->body,
            'deleted'         => $this->isDeleted(),
            'deleted_by_admin'=> $this->isDeleted() && $this->deleted_by !== null
                                && (int) $this->deleted_by !== (int) $this->sender_id,
            'file_url'        => $this->fileUrl(),
            'file_name'       => $this->file_name,
            'file_mime'       => $this->file_mime,
            'file_size_human' => $this->fileSizeHuman(),
            'reply_to'        => $this->replyTo ? [
                'id'     => $this->replyTo->id,
                'body'   => $this->replyTo->preview(),
                'sender' => $this->replyTo->sender?->full_name,
            ] : null,
            'sender' => $sender ? [
                'id'      => $sender->id,
                'name'    => $sender->full_name,
                'avatar'  => $sender->photo ? asset('storage/' . $sender->photo) : null,
                'initials'=> $sender->initials,
            ] : null,
            'is_mine'         => $isMine,
            'tick_state'      => $tick['state'],
            'delivered_at'    => $this->delivered_at?->toISOString(),
            'read_at'         => $tick['read_at']?->toISOString(),
            'created_at'      => $this->created_at->toISOString(),
            'time'            => $this->created_at->format('H:i'),
            'date_label'      => $this->created_at->isToday()
                ? 'Today'
                : ($this->created_at->isYesterday()
                    ? 'Yesterday'
                    : $this->created_at->format('d M Y')),
        ];
    }
}