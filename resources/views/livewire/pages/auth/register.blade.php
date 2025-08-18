<div>
    <form wire:submit="register" class="space-y-6">
        <flux:field>
            <flux:label for="name">Name</flux:label>
            <flux:input type="text" id="name" wire:model="form.name" placeholder="John Doe" required autofocus />
            <flux:error name="form.name" />
        </flux:field>

        <flux:field>
            <flux:label for="email">Email address</flux:label>
            <flux:input type="email" id="email" wire:model="form.email" placeholder="you@example.com" required />
            <flux:error name="form.email" />
        </flux:field>

        <flux:field>
            <flux:label for="password">Password</flux:label>
            <flux:input type="password" id="password" wire:model="form.password" placeholder="••••••••" required />
            <flux:error name="form.password" />
        </flux:field>

        <flux:field>
            <flux:label for="password_confirmation">Confirm Password</flux:label>
            <flux:input
                type="password"
                id="password_confirmation"
                wire:model="form.password_confirmation"
                placeholder="••••••••"
                required
            />
            <flux:error name="form.password_confirmation" />
        </flux:field>

        <flux:button type="submit" variant="primary" class="w-full">Create account</flux:button>
    </form>

    <div class="mt-2 text-center">
        <flux:text>
            Already have an account?
            <flux:link href="{{ route('login') }}" wire:navigate variant="primary">Sign in</flux:link>
        </flux:text>
    </div>
</div>
