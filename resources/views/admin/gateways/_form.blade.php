@csrf
<div class="space-y-6">
    <!-- Campos Nome, Logo, Status (sem alteração ) -->
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nome do Gateway</label>
        <div class="mt-1">
            <input type="text" name="name" id="name" class="block w-full px-4 py-3 bg-white dark:bg-black text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 rounded-lg shadow-sm focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white" value="{{ old('name', $gateway->name ?? '') }}" required placeholder="Ex: Mercado Pago">
        </div>
    </div>
    <div>
        <label for="logo_url" class="block text-sm font-medium text-slate-700 dark:text-slate-300">URL da Logo</label>
        <div class="mt-1">
            <input type="url" name="logo_url" id="logo_url" class="block w-full px-4 py-3 bg-white dark:bg-black text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 rounded-lg shadow-sm focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white" value="{{ old('logo_url', $gateway->logo_url ?? '') }}" placeholder="https://...">
        </div>
    </div>
    <div>
        <label for="is_active" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
        <div class="mt-1">
            <select id="is_active" name="is_active" class="block w-full px-4 py-3 bg-white dark:bg-black text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 rounded-lg shadow-sm focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white" required>
                <option value="1" @selected(old('is_active', $gateway->is_active ?? 1 ) == 1)>Ativo</option>
                <option value="0" @selected(old('is_active', $gateway->is_active ?? 1) == 0)>Inativo</option>
            </select>
        </div>
    </div>

    <!-- CONSTRUTOR DE SCHEMA DE CREDENCIAIS (NOVO) -->
    <div x-data="schemaBuilder({
    initialFields: {{ json_encode(old('fields', $gateway->credentials_schema['fields'] ?? [])) }}
})">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Schema de Credenciais</label>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Construa os campos que o cliente precisará preencher para este gateway.
        </p>

        <!-- Input oculto que guardará o JSON final -->
        <input type="hidden" name="credentials_schema" x-bind:value="getFinalJson()">

        <!-- Lista de Campos Dinâmicos -->
        <div class="mt-4 space-y-4">
            <template x-for="(field, index) in fields" :key="index">
                <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-black border border-gray-200 dark:border-white/5 rounded-lg">
                    <!-- Inputs para cada campo -->
                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label :for="'field_name_' + index" class="text-xs font-medium text-slate-600 dark:text-slate-400">Nome (ID)</label>
                            <input :id="'field_name_' + index" type="text" x-model="field.name" placeholder="ex: client_id"
                                   class="mt-1 block w-full text-sm px-3 py-2 bg-white dark:bg-black border border-gray-300 dark:border-white/10 rounded-md shadow-sm focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white">
                        </div>
                        <div>
                            <label :for="'field_label_' + index" class="text-xs font-medium text-slate-600 dark:text-slate-400">Rótulo (Label)</label>
                            <input :id="'field_label_' + index" type="text" x-model="field.label" placeholder="ex: Client ID"
                                   class="mt-1 block w-full text-sm px-3 py-2 bg-white dark:bg-black border border-gray-300 dark:border-white/10 rounded-md shadow-sm focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white">
                        </div>
                        <div>
                            <label :for="'field_type_' + index" class="text-xs font-medium text-slate-600 dark:text-slate-400">Tipo</label>
                            <select :id="'field_type_' + index" x-model="field.type"
                                    class="mt-1 block w-full text-sm px-3 py-2 bg-white dark:bg-black border border-gray-300 dark:border-white/10 rounded-md shadow-sm focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white">
                                <option value="text">Texto</option>
                                <option value="password">Senha</option>
                            </select>
                        </div>
                    </div>
                    <!-- Botão de Remover -->
                    <div class="pt-5">
                        <button type="button" @click="removeField(index)" title="Remover Campo"
                                class="p-2 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <div class="mt-4">
            <button type="button" @click="addField()"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-dashed border-gray-400 dark:border-white/10 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:border-black dark:hover:border-white hover:text-black dark:hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Adicionar Campo de Credencial
            </button>
        </div>
        <br>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Taxa Cartão (%)</label>
        <input type="number" step="0.01" name="card_fee_percentage"
               value="{{ old('card_fee_percentage', $gateway->card_fee_percentage ?? '') }}"
               class="mt-1 block w-full px-3 py-2 bg-white dark:bg-black text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 rounded-lg">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Taxa PIX (%)</label>
        <input type="number" step="0.01" name="pix_fee_percentage"
               value="{{ old('pix_fee_percentage', $gateway->pix_fee_percentage ?? '') }}"
               class="mt-1 block w-full px-3 py-2 bg-white dark:bg-black text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 rounded-lg">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Taxa Fixa (R$)</label>
        <input type="number" step="0.01" name="fixed_fee"
               value="{{ old('fixed_fee', $gateway->fixed_fee ?? '') }}"
               class="mt-1 block w-full px-3 py-2 bg-white dark:bg-black text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 rounded-lg">
    </div>
</div>

<div>
    <label for="order" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Ordem de Prioridade</label>
    <input
        type="number"
        name="order"
        id="order"
        min="1"
        class="mt-1 block w-full px-4 py-3 bg-white dark:bg-black text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 rounded-lg shadow-sm focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white"
        value="{{ old('order', $gateway->order ?? 1) }}"
        required
    >
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
        Gateways com ordem menor serão usados primeiro. Se falhar, tenta o próximo.
    </p>
</div>



        <!-- Botão para Adicionar Novo Campo -->
       
    </div>
</div>

<!-- Botões de Ação -->
<div class="mt-8 pt-5 border-t border-gray-200 dark:border-white/5">
    <div class="flex justify-end gap-x-4">
        <a href="{{ route('admin.gateways.index') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-white/10 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-black/5 dark:hover:bg-white/5 font-medium text-sm transition-colors">
            Cancelar
        </a>
        <button type="submit" class="inline-flex items-center justify-center space-x-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm transition-all duration-200 shadow-md hover:shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a1 1 0 011 1v7a1 1 0 11-2 0v-6h-4v5.586l-1.293-1.293zM4 4a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>
            <span>Salvar Gateway</span>
        </button>
    </div>
</div>

<script>
    function schemaBuilder(config ) {
        return {
            fields: config.initialFields || [],
            
            // Adiciona um novo campo em branco
            addField() {
                this.fields.push({ name: '', label: '', type: 'text' });
            },

            // Remove um campo pelo seu índice
            removeField(index) {
                this.fields.splice(index, 1);
            },

            // Gera o JSON final para ser enviado pelo formulário
            getFinalJson() {
                return JSON.stringify({ fields: this.fields });
            },

            // Inicializa o construtor
            init() {
                // Se não houver campos iniciais, adiciona um em branco para começar
                if (this.fields.length === 0) {
                    this.addField();
                }
            }
        }
    }
</script>
