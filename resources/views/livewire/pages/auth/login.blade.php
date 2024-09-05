<div>
    <form wire:submit="login">
        <p>
            <input type="email" wire:model="form.email" />
        </p>

        <p>
            <input type="password" wire:model="form.password" />
        </p>

        <p>
            <label for="remember">
                <input wire:model="form.remember" type="checkbox" />
                <span>Remember Me</span>
            </label>
        </p>

        <button type="submit">Login</button>
    </form>
</div>
