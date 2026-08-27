<section class="space-y-6">
    <header>
        <h2 class="teacher-section-title teacher-section-title--danger">Eliminar cuenta</h2>
        <p class="teacher-meta">Esta acción elimina permanentemente la cuenta y sus datos asociados. Descarga previamente cualquier información que necesites conservar.</p>
    </header>

    <button type="button" class="teacher-btn teacher-btn-danger-subtle" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">Eliminar mi cuenta</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="teacher-section-title teacher-section-title--danger">¿Confirmas que deseas eliminar tu cuenta?</h2>
            <p class="teacher-meta">La eliminación es permanente. Escribe tu contraseña para confirmar.</p>

            <div class="mt-6">
                <x-input-label for="password" value="Contraseña" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Contraseña"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-2">
                <button type="button" class="teacher-btn teacher-btn-muted" x-on:click="$dispatch('close')">Cancelar</button>
                <button type="submit" class="teacher-btn teacher-btn-danger">Eliminar cuenta definitivamente</button>
            </div>
        </form>
    </x-modal>
</section>
