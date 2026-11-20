<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Wishlist;

class WishlistController extends Controller
{
    private Wishlist $wishlistModel;

    public function __construct()
    {
        $this->wishlistModel = new Wishlist();
    }

    public function index(): void
    {
        Auth::requireStudent();
        $user = Auth::user();
        $wishlists = $this->wishlistModel->getByUser((int) $user['id']);

        $this->view('wishlists/index', [
            'title'     => 'Wishlist Saya - TIF Hub',
            'user'      => $user,
            'wishlists' => $wishlists,
        ]);
    }

    public function add(string $projectId): void
    {
        Auth::requireStudent();

        if (!$this->validateCsrf()) {
            $this->backWithError('Token keamanan tidak valid.');
        }

        $userId = (int) Auth::id();
        $this->wishlistModel->add($userId, (int) $projectId);

        Session::flash('success', 'Proyek berhasil ditambahkan ke wishlist.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/projects');
    }

    public function remove(string $projectId): void
    {
        Auth::requireStudent();

        if (!$this->validateCsrf()) {
            $this->backWithError('Token keamanan tidak valid.');
        }

        $userId = (int) Auth::id();
        $this->wishlistModel->remove($userId, (int) $projectId);

        Session::flash('success', 'Proyek dihapus dari wishlist.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/wishlists');
    }
}
