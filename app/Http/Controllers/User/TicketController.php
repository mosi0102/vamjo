<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Support\Code;
use App\Support\Presenter;
use App\Support\Text;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = $request->user()->tickets()
            ->withCount(['messages'])
            ->withCount(['messages as unread_count' => fn ($query) => $query
                ->where('author_role', 'support')
                ->where('is_read', false)])
            ->latest('last_reply_at')
            ->latest()
            ->get()
            ->map(fn (Ticket $ticket) => Presenter::ticketRow($ticket))
            ->all();

        return view('userPanel.ticket.index', compact('tickets'));
    }

    public function create(): View
    {
        return view('userPanel.ticket.create', [
            'depts' => config('vamjou.ticket_departments'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $department = Text::canonical($request->input('department'), config('vamjou.ticket_departments'));

        if (! $department) {
            throw ValidationException::withMessages(['department' => 'بخش مرتبط انتخاب‌شده معتبر نیست.']);
        }

        $request->merge([
            'department' => $department,
            'priority' => Text::normalize($request->input('priority')),
        ]);

        $data = $request->validate([
            'subject' => ['required', 'string', 'min:4', 'max:80'],
            'department' => ['required', Rule::in(config('vamjou.ticket_departments'))],
            'priority' => ['required', Rule::in(array_keys(config('vamjou.ticket_priorities')))],
            'message' => ['required', 'string', 'min:10', 'max:1500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:'.config('vamjou.uploads.max_size')],
        ], [], [
            'subject' => 'موضوع تیکت', 'department' => 'بخش مرتبط',
            'priority' => 'اولویت', 'message' => 'متن پیام',
        ]);

        $ticket = $request->user()->tickets()->create([
            'code' => Code::make('tickets', 'code', 6, 1),
            'subject' => $data['subject'],
            'department' => $data['department'],
            'priority' => $data['priority'],
            'status' => Ticket::STATUS_PENDING,
            'last_reply_at' => now(),
        ]);

        $this->attachMessage($ticket, $request, $data['message']);

        ActivityLog::record($request->user(), 'ticket.created', "ثبت تیکت {$ticket->code}");

        return redirect()->route('user.tickets.show', $ticket)
            ->with('success', 'تیکت شما ثبت شد؛ پشتیبانی حداکثر تا ۲۴ ساعت پاسخ می‌دهد.');
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $this->authorizeOwner($request, $ticket);

        $ticket->load('messages');

        // پیام‌های کاربر خوانده و یادداشت‌های داخلی پشتیبانی مخفی می‌شوند
        $ticket->messages()->where('author_role', '!=', 'internal')->update(['is_read' => true]);

        $messages = $ticket->messages
            ->filter(fn ($message) => $message->author_role !== 'internal')
            ->map(fn ($message) => Presenter::ticketMessage($message))
            ->all();

        return view('userPanel.ticket.show', [
            'ticket' => Presenter::ticketDetail($ticket),
            'messages' => $messages,
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeOwner($request, $ticket);

        if ($ticket->isClosed()) {
            return back()->withErrors(['message' => 'این تیکت بسته شده است؛ لطفاً تیکت جدیدی ثبت کنید.']);
        }

        $data = $request->validate([
            'message' => ['nullable', 'string', 'required_without:attachment', 'min:2', 'max:1500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:'.config('vamjou.uploads.max_size')],
        ], [], ['message' => 'متن پیام']);

        $this->attachMessage($ticket, $request, $data['message'] ?? '');

        $ticket->update(['status' => Ticket::STATUS_PENDING, 'last_reply_at' => now()]);

        return back()->with('success', 'پاسخ شما ثبت شد.');
    }

    public function close(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeOwner($request, $ticket);

        $ticket->update(['status' => Ticket::STATUS_CLOSED, 'closed_at' => now()]);

        ActivityLog::record($request->user(), 'ticket.closed', "بستن تیکت {$ticket->code}");

        return redirect()->route('user.tickets.index')->with('success', 'تیکت بسته شد.');
    }

    private function attachMessage(Ticket $ticket, Request $request, string $body): void
    {
        $file = $request->file('attachment');
        $path = $file?->store("tickets/{$ticket->id}", config('vamjou.uploads.disk'));

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'author_role' => 'user',
            'body' => $body,
            'attachment_path' => $path,
            'attachment_name' => $file?->getClientOriginalName(),
            'attachment_size' => $file ? round(($file->getSize() ?: 0) / 1024).' KB' : null,
        ]);
    }

    private function authorizeOwner(Request $request, Ticket $ticket): void
    {
        abort_unless($ticket->user_id === $request->user()->id, 403, 'این تیکت متعلق به شما نیست.');
    }
}
