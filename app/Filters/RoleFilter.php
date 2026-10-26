<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Pembatasan hak akses berbasis peran (admin, analis, pimpinan)
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn') || !$session->get('user_id')) {
            return redirect()->to(site_url('login'))->with('error', 'Silakan login terlebih dahulu.');
        }

        if (!empty($arguments)) {
            $userRole = (string) $session->get('role');
            if (!in_array($userRole, $arguments, true)) {
                return redirect()->to(site_url('/'))->with('error', 'Akses ditolak: Anda tidak memiliki izin untuk halaman tersebut.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada tindakan pasca-eksekusi khusus
    }
}
