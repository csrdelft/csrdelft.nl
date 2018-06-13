<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\View\Formulieren\AccountForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use function CsrDelft\getDateTime;
use function CsrDelft\setMelding;

class AccountController extends Controller
{
    const SESSION_SU_KEY = 'suedFrom';

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * @param Account|null $account
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function bewerken(Account $account)
    {
        $this->authorize('view', $account);

        $form = new AccountForm($account);

        if ($form->validate()) {
            $this->authorize('update', $account);

            $this->update($account, $form->getValues());

            $passPlain = $form->findByName('wijzigww')->getValue();

            if ($passPlain) {
                $this->updatePassword($account, $passPlain);
            }

            return back();
        } else {
            return view('account.bewerken', ['account' => $account, 'form' => $form]);
        }
    }

    public function su(Request $request, Account $account)
    {
        if (Auth::maySuTo($account)) {
            $request->session()->put(self::SESSION_SU_KEY, Auth::user());

            Auth::login($account);

            setMelding('U bekijkt de webstek nu als ' . $account->profiel->getNaam('volledig') . '!', 1);
        }

        return back();
    }

    public function endsu(Request $request)
    {
        if (Auth::isSued()) {
            /** @var Account $account */
            $account = $request->session()->pull(self::SESSION_SU_KEY);

            Auth::login($account);

            setMelding('Switch-useractie is beëindingd.', 1);
        } else {
            setMelding('Niet gesued!', 0);
        }

        return back();
    }

    private function updatePassword(Account $account, $password)
    {
        $account->pass_hash = Hash::make($password);
        $account->pass_since = getDateTime();
        $account->save();

        Auth::guard()->login($account);
    }

    private function update(Account $account, array $data)
    {
        $account->username = $data['username'];
        $account->email = $data['email'];
        $account->perm_role = $data['perm_role'];
        $account->save();

        Auth::guard()->login($account);
    }
}
