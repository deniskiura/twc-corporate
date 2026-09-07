import CompanyUsersController from '@/actions/App/Http/Controllers/Api/CompanyUsersController';
import InvitationController from '@/actions/App/Http/Controllers/Api/InvitationController';
import { createApiClient } from '@/lib/api';
import type { Invitation, SponsoredUser, TeamResponse } from '@/types';

/**
 * The calls the team screen makes. URLs come from Wayfinder, so a renamed
 * route fails the type check instead of a user's click.
 */
export function useTeamApi(token: string) {
    const api = createApiClient(token);

    return {
        list: () => api.get<TeamResponse>(CompanyUsersController.index.url()),

        invite: (email: string, planId: number) =>
            api.post<{ data: Invitation }>(InvitationController.store.url(), {
                email,
                plan_id: planId,
            }),

        resend: (seat: SponsoredUser) =>
            api.post<{ data: Invitation }>(
                InvitationController.resend.url({ sponsorship: seat.id }),
            ),

        revoke: (seat: SponsoredUser) =>
            api.delete<void>(
                InvitationController.destroy.url({ sponsorship: seat.id }),
            ),
    };
}

export type TeamApi = ReturnType<typeof useTeamApi>;
