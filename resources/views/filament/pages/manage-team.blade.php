<x-filament::page>
    <div class="space-y-6">
        <x-filament::card>
            <h2 class="text-lg font-semibold tracking-tight">Invite someone new</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Send an email invitation. When they accept, they join <strong>{{ auth()->user()?->currentCompany?->name }}</strong> with the role you choose.
            </p>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                Configure mail in <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-gray-800">.env</code> (<code>MAIL_MAILER</code>, <code>MAIL_HOST</code>, etc.) so invitation emails are delivered.
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
                @if($this->getCompanySettingsUrl())
                    <x-filament::button tag="a" :href="$this->getCompanySettingsUrl()" icon="heroicon-o-cog">
                        Open company settings (invite &amp; manage members)
                    </x-filament::button>
                @endif
                <x-filament::button tag="a" :href="$this->getInvitationsUrl()" color="gray" icon="heroicon-o-mail">
                    View pending invitations
                </x-filament::button>
            </div>
        </x-filament::card>

        <x-filament::card>
            <h2 class="text-lg font-semibold tracking-tight">Add someone who already has an account</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                If the person is already registered in this application, add them to the company by email from <strong>Company settings</strong> (same page as invitations). You can also create their login first, then attach them below.
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
                <x-filament::button tag="a" :href="$this->getCreateUserUrl()" icon="heroicon-o-user-add">
                    Create user account
                </x-filament::button>
                @if($this->getCompanySettingsUrl())
                    <x-filament::button tag="a" :href="$this->getCompanySettingsUrl()" color="gray" icon="heroicon-o-user-group">
                        Add existing user to company
                    </x-filament::button>
                @endif
            </div>
        </x-filament::card>
    </div>
</x-filament::page>
