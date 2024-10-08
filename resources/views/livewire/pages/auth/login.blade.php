<div>
    <form wire:submit="login">
        <p>
            <label for="email">Email</label>
            <input type="email" id="email" wire:model="form.email" />
            @error('form.email') <span>{{ $message }}</span> @enderror
        </p>

        <p>
            <label for="password">Password</label>
            <input type="password" id="password" wire:model="form.password" />
        </p>

        <p>
            <label for="remember">
                <input wire:model="form.remember" id="remember" type="checkbox" />
                <span>Remember Me</span>
            </label>
        </p>

        <button type="submit">Login</button>
    </form>

    <p><a href="{{ route('register') }}" wire:navigate>Register an Account</a></p>
</div>
