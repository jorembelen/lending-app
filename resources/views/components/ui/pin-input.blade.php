@props([
    'digits' => 4,
    'model'  => 'pin',
])

<div
    class="flex gap-3 justify-center"
    x-data="{
        pin: Array({{ $digits }}).fill(''),
        get value() { return this.pin.join('') },
        focusNext(index) {
            const next = this.$refs['pin' + (index + 1)];
            if (next) next.focus();
        },
        focusPrev(index) {
            const prev = this.$refs['pin' + (index - 1)];
            if (prev) prev.focus();
        },
        handleInput(index, e) {
            const val = e.target.value.replace(/\D/g, '').slice(-1);
            this.pin[index] = val;
            this.$wire.set('{{ $model }}', this.value);
            if (val) this.focusNext(index);
        },
        handleKey(index, e) {
            if (e.key === 'Backspace' && !this.pin[index]) this.focusPrev(index);
        }
    }"
>
    @for($i = 0; $i < $digits; $i++)
        <input
            x-ref="pin{{ $i }}"
            type="password"
            inputmode="numeric"
            maxlength="1"
            @input="handleInput({{ $i }}, $event)"
            @keydown="handleKey({{ $i }}, $event)"
            :value="pin[{{ $i }}]"
            class="w-14 h-14 text-center text-headline-md font-sans font-bold bg-surface-container-low border border-white/10 rounded-xl text-on-surface focus:outline-none focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed"
        />
    @endfor
</div>
