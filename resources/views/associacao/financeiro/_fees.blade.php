@php
    $methodTitles = [
        'pix' => 'PIX',
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    @forelse ($fees as $fee)
        @php
            $title = $methodTitles[$fee->payment_method] ?? ucfirst($fee->payment_method);

            $formattedValue = '';
            if ($fee->percentage_fee > 0) {
                $formattedValue .= number_format($fee->percentage_fee, 2, ',', '.') . '%';
            }
            if ($fee->fixed_fee > 0) {
                if ($formattedValue !== '') {
                    $formattedValue .= ' + ';
                }
                $formattedValue .= 'R$ ' . number_format($fee->fixed_fee, 2, ',', '.');
            }
            if ($formattedValue === '') {
                $formattedValue = 'Isento';
            }
        @endphp

        <div class="bg-white dark:bg-black p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-4">
                {{ $title }}
            </h3>

            <div class="space-y-3">
                <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                    <span class="text-slate-600 dark:text-slate-300">Tipo de taxa</span>
                    <span class="font-medium text-slate-900 dark:text-white">Por venda</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-slate-600 dark:text-slate-300">Valor</span>
                    <span class="font-medium text-slate-900 dark:text-white">
                        {{ $formattedValue }}
                    </span>
                </div>
            </div>
        </div>

    @empty
        <div class="col-span-full bg-white dark:bg-black p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm text-center">
            <p class="text-slate-500 dark:text-slate-400">
                Nenhuma estrutura de taxas foi configurada para sua conta.
            </p>
        </div>
    @endforelse

    <div class="bg-white dark:bg-black p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-4">
            Saques
        </h3>

        <div class="space-y-3">
            <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                <span class="text-slate-600 dark:text-slate-300">Tipo</span>
                <span class="font-medium text-slate-900 dark:text-white">Manual</span>
            </div>

            <div class="flex justify-between">
                <span class="text-slate-600 dark:text-slate-300">Taxa por saque</span>
                <span class="font-medium text-slate-900 dark:text-white">
                    R$ 5,00
                </span>
            </div>
        </div>
    </div>

</div>
