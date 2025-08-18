<div>
    <form wire:submit="login" class="space-y-6">
        <flux:field>
            <flux:label for="email">Email address</flux:label>
            <flux:input 
                type="email" 
                id="email" 
                wire:model="form.email"
                placeholder="you@example.com"
                required
                autofocus
            />
            <flux:error name="form.email" />
        </flux:field>

        <flux:field>
            <flux:label for="password">Password</flux:label>
            <flux:input 
                type="password" 
                id="password" 
                wire:model="form.password"
                placeholder="••••••••"
                required
            />
            <flux:error name="form.password" />
        </flux:field>

        <div class="flex items-center justify-between">
            <flux:checkbox 
                wire:model="form.remember" 
                id="remember"
                label="Remember me"
            />
            <flux:link href="#" variant="primary" class="text-sm">
                Forgot password?
            </flux:link>
        </div>

        <flux:button type="submit" variant="primary" class="w-full">
            Sign in
        </flux:button>
    </form>

    <div class="text-center mt-2">
        <flux:text>
            Don't have an account?
            <flux:link href="{{ route('register') }}" wire:navigate variant="primary">
                Create an account
            </flux:link>
        </flux:text>
    </div>
</div>