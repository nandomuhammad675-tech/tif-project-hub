<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Ticket;
use App\Models\Category;

class TicketController extends Controller
{
    private Ticket $ticketModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        Auth::requireStudent();
        $user = Auth::user();
        $tickets = $this->ticketModel->getByUser((int) $user['id']);

        $this->view('tickets/index', [
            'title'   => 'Tiket Saya - TIF Hub',
            'user'    => $user,
            'tickets' => $tickets,
        ]);
    }

    public function create(): void
    {
        Auth::requireStudent();

        $this->view('tickets/create', [
            'title'      => 'Buat Tiket - TIF Hub',
            'user'       => Auth::user(),
            'categories' => $this->categoryModel->forTickets(),
            'csrf'       => Session::getCsrfToken(),
        ]);
    }

    public function store(): void
    {
        Auth::requireStudent();

        if (!$this->validateCsrf()) {
            $this->backWithError('Token keamanan tidak valid.');
        }

        $title = trim($this->input('title', ''));
        $categoryId = (int) $this->input('category_id', 0);
        $priority = $this->input('priority', 'normal');
        $description = trim($this->input('description', ''));

        if ($title === '' || $description === '') {
            Session::flash('error', 'Judul dan deskripsi wajib diisi.');
            $this->redirect('/tickets/create');
        }

        if (!in_array($priority, ['rendah', 'normal', 'tinggi'], true)) {
            $priority = 'normal';
        }

        $id = $this->ticketModel->createTicket([
            'user_id'     => Auth::id(),
            'category_id' => $categoryId ?: null,
            'title'       => $title,
            'description' => $description,
            'priority'    => $priority,
            'status'      => 'open',
        ]);

        Session::flash('success', 'Tiket berhasil dibuat.');
        $this->redirect('/tickets/' . $id);
    }

    public function show(string $id): void
    {
        Auth::requireStudent();
        $user = Auth::user();
        $ticket = $this->ticketModel->findWithDetails((int) $id);

        if (!$ticket || (int) $ticket['user_id'] !== (int) $user['id']) {
            Session::flash('error', 'Tiket tidak ditemukan.');
            $this->redirect('/tickets');
        }

        $replies = $this->ticketModel->getReplies((int) $id, false);

        $this->view('tickets/show', [
            'title'   => $ticket['ticket_code'] . ' - TIF Hub',
            'user'    => $user,
            'ticket'  => $ticket,
            'replies' => $replies,
            'csrf'    => Session::getCsrfToken(),
        ]);
    }

    public function reply(string $id): void
    {
        Auth::requireStudent();

        if (!$this->validateCsrf()) {
            $this->backWithError('Token keamanan tidak valid.');
        }

        $user = Auth::user();
        $ticket = $this->ticketModel->findWithDetails((int) $id);

        if (!$ticket || (int) $ticket['user_id'] !== (int) $user['id']) {
            Session::flash('error', 'Tiket tidak ditemukan.');
            $this->redirect('/tickets');
        }

        if (in_array($ticket['status'], ['selesai', 'ditolak'], true)) {
            Session::flash('error', 'Tiket sudah ditutup, tidak bisa membalas.');
            $this->redirect('/tickets/' . $id);
        }

        $message = trim($this->input('message', ''));
        if ($message === '') {
            Session::flash('error', 'Pesan tidak boleh kosong.');
            $this->redirect('/tickets/' . $id);
        }

        $this->ticketModel->addReply((int) $id, (int) $user['id'], $message, false);
        Session::flash('success', 'Balasan terkirim.');
        $this->redirect('/tickets/' . $id);
    }
}
