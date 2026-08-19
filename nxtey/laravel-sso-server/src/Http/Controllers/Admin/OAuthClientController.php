<?php

namespace Nxtey\SsoServer\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;

class OAuthClientController extends Controller
{
    public function index(ClientRepository $clientRepository)
    {
        $clients = $clientRepository->all()->filter(function ($client) {
            return ! $client->personal_access_client && ! $client->password_client;
        });

        return view('sso-server::admin.clients.index', compact('clients'));
    }

    public function store(Request $request, ClientRepository $clientRepository)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => 'required|url',
        ]);

        $client = $clientRepository->create(null, $validated['name'], $validated['redirect']);

        return redirect()->route('sso-server.admin.clients.index')
            ->with('success', "Client created! Secret: {$client->plainSecret} (Save this now, it won't be shown again).");
    }

    public function destroy($id, ClientRepository $clientRepository)
    {
        $client = $clientRepository->findActiveById($id);
        if ($client) {
            $client->revoke();
        }
        return redirect()->route('sso-server.admin.clients.index')->with('success', 'Client revoked successfully.');
    }
}