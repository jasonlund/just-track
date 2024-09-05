<div>
    <form wire:submit="register">
        <p>
            <label for="name">Name</label>
            <input type="text" id="name" wire:model="form.name" />
            @error('form.name') <span>{{ $message }}</span> @enderror
        </p>

        <p>
            <label for="email">Email</label>
            <input type="email" id="email" wire:model="form.email" />
            @error('form.email') <span>{{ $message }}</span> @enderror
        </p>

        <p>
            <label for="password">Password</label>
            <input type="password" id="password" wire:model="form.password" />
            @error('form.password') <span>{{ $message }}</span> @enderror
        </p>

        <p>
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" wire:model="form.password_confirmation" />
        </p>

        <button type="submit">Register</button>
    </form>

    <p><a href="{{ route('login') }}" wire:navigate>Login To an Existing Account</a></p>
</div>
