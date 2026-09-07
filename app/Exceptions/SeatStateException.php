<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Thrown when a seat is used in a state that doesn't allow it, such as
 * accepting an invite that was withdrawn or resuming someone who was never
 * suspended. Carries the HTTP status the API should answer with.
 */
class SeatStateException extends RuntimeException
{
    private function __construct(string $message, private readonly int $status)
    {
        parent::__construct($message);
    }

    public static function revoked(): self
    {
        return new self('This invite has been withdrawn by the company.', 410);
    }

    public static function alreadyAccepted(): self
    {
        return new self('This invite has already been accepted. Log in instead.', 409);
    }

    public static function sponsoredElsewhere(): self
    {
        return new self('This account is already sponsored by another company.', 409);
    }

    public static function notPending(): self
    {
        return new self('Only pending invites can be resent or withdrawn.', 409);
    }

    public static function notJoined(): self
    {
        return new self('Only employees who have joined can be suspended or removed.', 409);
    }

    public static function notSuspended(): self
    {
        return new self('Only suspended employees can be resumed.', 409);
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $this->getMessage()], $this->status);
        }

        return back()->withErrors(['invite' => $this->getMessage()]);
    }
}
