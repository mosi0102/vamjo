<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Fa;
use App\Support\Presenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = Ticket::query()
            ->with(['user', 'assignee'])
            ->when($request->filled('state'), function ($query) use ($request) {
                match ($request->string('state')->toString()) {
                    'pending' => $query->pendingAnswer(),
                    'answered' => $query->where('status', Ticket::STATUS_ANSWERED),
                    'closed' => $query->where('status', Ticket::STATUS_CLOSED),
                    'mine' => $query->assignedTo($request->user()),
                    default => $query,
                };
            })
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')->toString()))
            ->when($request->filled('user'), fn ($query) => $query->where('user_id', (int) $request->input('user')))
            ->latest('last_reply_at')
            ->paginate(20)
            ->withQueryString();

        return view('adminPanel.ticket.index', [
            'rows' => collect($tickets->items())
                ->map(fn (Ticket $ticket) => Presenter::adminTicketRow($ticket, $request->user()->id))
                ->all(),
            'paginator' => $tickets,
        ]);
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $ticket->load(['messages.user', 'user', 'assignee']);

        return view('adminPanel.ticket.show', [
            'ticket' => [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'title' => $ticket->subject,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'dept' => $ticket->department,
                'date' => Fa::dateTime($ticket->created_at, 'j F Y – H:i'),
                'assignee' => $ticket->assignee?->display_name ?? '',
            ],
            'user' => [
                'id' => $ticket->user?->id,
                'name' => $ticket->user?->display_name ?? '',
                'mobile' => $ticket->user?->mobile ?? '',
                'ver' => $ticket->user?->kyc_label ?? '',
                'ads' => $ticket->user?->ads()->count() ?? 0,
                'reqs' => $ticket->user ? $ticket->user->purchases()->count() + $ticket->user->sales()->count() : 0,
            ],
            'messages' => $ticket->messages->map(fn ($message) => Presenter::ticketMessage($message))->all(),
            'canned' => [
                'سلام و وقت بخیر؛ درخواست شما در حال بررسی است و نتیجه به‌زودی اعلام می‌شود.',
                'لطفاً تصویر واضح‌تری از مدرک ارسال کنید.',
                'مشکل شما برطرف شد. در صورت نیاز دوباره پیام بدهید.',
                'برای پیگیری بیشتر، شماره تراکنش و شناسه وام را ارسال کنید.',
            ],
            'ticketModel' => $ticket,
        ]);
    }

    /** پاسخ پشتیبان */
    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:1500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:'.config('vamjou.uploads.max_size')],
        ], [], ['message' => 'متن پاسخ']);

        $file = $request->file('attachment');

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'author_role' => 'support',
            'body' => $data['message'],
            'attachment_path' => $file?->store("tickets/{$ticket->id}", config('vamjou.uploads.disk')),
            'attachment_name' => $file?->getClientOriginalName(),
            'attachment_size' => $file ? round(($file->getSize() ?: 0) / 1024).' KB' : null,
            'is_read' => true,
        ]);

        $ticket->update([
            'status' => Ticket::STATUS_ANSWERED,
            'assignee_id' => $ticket->assignee_id ?: $request->user()->id,
            'last_reply_at' => now(),
        ]);

        $ticket->messages()->where('author_role', 'user')->update(['is_read' => true]);

        ActivityLog::record($ticket->user, 'ticket.answered', "پاسخ به تیکت {$ticket->code}", [
            'ticket_id' => $ticket->id, 'agent' => $request->user()->id,
        ]);

        return back()->with('success', 'پاسخ ثبت شد.');
    }

    /** تغییر وضعیت/اولویت/مسئول تیکت */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['nullable', Rule::in([Ticket::STATUS_PENDING, Ticket::STATUS_ANSWERED, Ticket::STATUS_CLOSED])],
            'priority' => ['nullable', Rule::in(array_keys(config('vamjou.ticket_priorities')))],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $ticket->update(array_filter([
            'status' => $data['status'] ?? null,
            'priority' => $data['priority'] ?? null,
            'assignee_id' => $data['assignee_id'] ?? null,
        ], fn ($value) => $value !== null));

        if ($ticket->status === Ticket::STATUS_CLOSED && ! $ticket->closed_at) {
            $ticket->update(['closed_at' => now()]);
        }

        return back()->with('success', 'تیکت به‌روزرسانی شد.');
    }

    public function close(Request $request, Ticket $ticket): RedirectResponse
    {
        $ticket->update(['status' => Ticket::STATUS_CLOSED, 'closed_at' => now()]);

        ActivityLog::record($ticket->user, 'ticket.closed', "بستن تیکت {$ticket->code} توسط پشتیبانی");

        return back()->with('success', 'تیکت بسته شد.');
    }

    /** لیست کارشناسان برای انتساب تیکت */
    public function agents(): array
    {
        return User::query()->staff()->get()->all();
    }
}
