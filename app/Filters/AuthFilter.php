<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Pastikan pengguna telah terautentikasi sebelum mengakses rute internal
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn') || !$session->get('user_id')) {
            $session->set('redirect_url', current_url());
            return redirect()->to(site_url('login'))->with('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada tindakan pasca-eksekusi khusus
    }
}
