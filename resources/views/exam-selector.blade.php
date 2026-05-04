<div x-data="examSelector()" class="space-y-6">
    {{-- SEARCH BAR --}}
    <div class="mb-6 flex items-center gap-3 px-4 py-3 border border-gray-300 rounded-lg shadow-sm bg-white dark:bg-gray-700 dark:border-gray-600 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent transition">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 shrink-0 text-gray-400 dark:text-gray-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input 
            type="text"
            x-model="search"
            @input="filterExams()"
            placeholder="Buscar exámenes por nombre..."
            class="flex-1 bg-transparent border-none outline-none focus:ring-0 text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-400"
        >
    </div>

    {{-- LABORATORIO BLOCK --}}
    @if ($laboratoryCategories->count() > 0 && ($orderType ?? 'laboratorio') === 'laboratorio')
        <div class="border border-blue-200 dark:border-blue-800 rounded-lg overflow-hidden shadow-sm dark:bg-gray-800">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 px-4 py-3 border-b border-blue-200 dark:border-blue-800">
                <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                    </svg>
                    Laboratorio
                    <span class="text-sm font-normal text-blue-600 dark:text-blue-300" x-text="`(${getVisibleLabExamsCount()} exámenes)`"></span>
                </h3>
            </div>
            
            <div class="divide-y divide-blue-100 dark:divide-blue-700 p-4 space-y-3">
                @foreach ($laboratoryCategories as $category)
                    <div 
                        class="category-item" 
                        x-data="{ expanded: false }"
                        x-show="isCategoryVisible('lab', {{ $category->id }})"
                        x-transition
                    >
                        <button 
                            type="button"
                            @click="expanded = !expanded"
                            class="w-full flex items-center justify-between py-2 px-3 hover:bg-blue-50 dark:hover:bg-blue-700 rounded-lg transition text-left"
                        >
                            <div class="flex items-center gap-3 flex-1">
                                <svg x-show="!expanded" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-500 dark:text-blue-400 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                    <svg x-show="expanded" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-blue-500 dark:text-blue-400 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $category->name }}</span>
                                <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 px-2 py-1 rounded-full ml-auto" x-text="`${getVisibleExamsInCategory('lab', {{ $category->id }}).length}`"></span>
                            </div>
                        </button>

                        <div x-show="expanded" x-transition class="ml-8 space-y-2 mt-2">
                            @foreach ($category->exams as $exam)
                                <label 
                                    class="exam-checkbox flex items-center gap-3 p-2 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-700 transition"
                                    data-exam-id="{{ $exam->id }}"
                                    data-exam-name="{{ strtolower($exam->name) }}"
                                    x-show="isExamVisible('lab', {{ $category->id }}, '{{ strtolower($exam->name) }}')"
                                    x-transition
                                >
                                    <input 
                                        type="checkbox"
                                        value="{{ $exam->id }}"
                                        :checked="isExamChecked({{ $exam->id }})"
                                        @change="toggleExam({{ $exam->id }})"
                                        class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 cursor-pointer"
                                    >
                                    <div class="flex-1">
                                        <span class="text-gray-800 dark:text-gray-200 font-medium">{{ $exam->name }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">BOB {{ number_format($exam->price, 2) }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- IMAGEN BLOCK --}}
    @if ($imagingCategories->count() > 0 && ($orderType ?? 'laboratorio') === 'imagen')
        <div class="border border-green-200 dark:border-green-800 rounded-lg overflow-hidden shadow-sm dark:bg-gray-800">
            <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900 dark:to-green-800 px-4 py-3 border-b border-green-200 dark:border-green-800">
                <h3 class="text-lg font-bold text-green-900 dark:text-green-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                    Imagen
                    <span class="text-sm font-normal text-green-600 dark:text-green-300" x-text="`(${getVisibleImgExamsCount()} exámenes)`"></span>
                </h3>
            </div>
            
            <div class="divide-y divide-green-100 dark:divide-green-700 p-4 space-y-3">
                @foreach ($imagingCategories as $category)
                    <div 
                        class="category-item" 
                        x-data="{ expanded: false }"
                        x-show="isCategoryVisible('img', {{ $category->id }})"
                        x-transition
                    >
                        <button 
                            type="button"
                            @click="expanded = !expanded"
                            class="w-full flex items-center justify-between py-2 px-3 hover:bg-green-50 dark:hover:bg-green-700 rounded-lg transition text-left"
                        >
                            <div class="flex items-center gap-3 flex-1">
                                <svg x-show="!expanded" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-green-500 dark:text-green-400 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                    <svg x-show="expanded" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-green-500 dark:text-green-400 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $category->name }}</span>
                                <span class="text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 px-2 py-1 rounded-full ml-auto" x-text="`${getVisibleExamsInCategory('img', {{ $category->id }}).length}`"></span>
                            </div>
                        </button>

                        <div x-show="expanded" x-transition class="ml-8 space-y-2 mt-2">
                            @foreach ($category->exams as $exam)
                                <label 
                                    class="exam-checkbox flex items-center gap-3 p-2 rounded-lg cursor-pointer hover:bg-green-50 dark:hover:bg-green-700 transition"
                                    data-exam-id="{{ $exam->id }}"
                                    data-exam-name="{{ strtolower($exam->name) }}"
                                    x-show="isExamVisible('img', {{ $category->id }}, '{{ strtolower($exam->name) }}')"
                                    x-transition
                                >
                                    <input 
                                        type="checkbox"
                                        value="{{ $exam->id }}"
                                        :checked="isExamChecked({{ $exam->id }})"
                                        @change="toggleExam({{ $exam->id }})"
                                        class="w-4 h-4 text-green-600 rounded focus:ring-2 focus:ring-green-500 dark:bg-gray-600 dark:border-gray-500 cursor-pointer"
                                    >
                                    <div class="flex-1">
                                        <span class="text-gray-800 dark:text-gray-200 font-medium">{{ $exam->name }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">BOB {{ number_format($exam->price, 2) }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- EMPTY STATE --}}
    <div 
        x-show="getVisibleLabExamsCount() === 0 && getVisibleImgExamsCount() === 0 && search.length > 0" 
        x-transition
        class="text-center py-8 text-gray-500 dark:text-gray-400"
    >
        <div class="flex justify-center mb-2 text-gray-300 dark:text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </div>
        <p class="text-lg font-medium">No se encontraron exámenes</p>
        <p class="text-sm">Intenta con otro término de búsqueda</p>
    </div>
</div>

<script>
function examSelector() {
    const laboratoryCategories = @json($laboratoryCategories);
    const imagingCategories = @json($imagingCategories);
    
    return {
        search: '',
        selectedExams: [],

        init() {
            this.$nextTick(() => {
                const current = this.$wire.get('data.exams');
                this.selectedExams = Array.isArray(current)
                    ? current.map(Number)
                    : [];
            });
        },

        isExamChecked(examId) {
            return this.selectedExams.includes(Number(examId));
        },

        toggleExam(examId) {
            const id = Number(examId);
            const idx = this.selectedExams.indexOf(id);
            if (idx === -1) {
                this.selectedExams.push(id);
            } else {
                this.selectedExams.splice(idx, 1);
            }
            this.$wire.set('data.exams', this.selectedExams);
        },

        filterExams() {
            // Alpine reacciona automáticamente a los cambios en visibilidad
        },
        
        isCategoryVisible(type, categoryId) {
            const categories = type === 'lab' ? laboratoryCategories : imagingCategories;
            const category = categories.find(c => c.id === categoryId);
            
            if (!category) return false;
            if (!this.search) return true;
            
            // Mostrar categoría si tiene exámenes visibles
            return category.exams.some(exam => 
                exam.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        
        isExamVisible(type, categoryId, examName) {
            if (!this.search) return true;
            
            return examName.toLowerCase().includes(this.search.toLowerCase());
        },
        
        getVisibleExamsInCategory(type, categoryId) {
            const categories = type === 'lab' ? laboratoryCategories : imagingCategories;
            const category = categories.find(c => c.id === categoryId);
            
            if (!category) return [];
            
            if (!this.search) return category.exams;
            
            return category.exams.filter(exam => 
                exam.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        
        getVisibleLabExamsCount() {
            if (!this.search) {
                return laboratoryCategories.reduce((sum, cat) => sum + cat.exams.length, 0);
            }
            
            return laboratoryCategories.reduce((sum, cat) => {
                const visible = cat.exams.filter(exam => 
                    exam.name.toLowerCase().includes(this.search.toLowerCase())
                );
                return sum + visible.length;
            }, 0);
        },
        
        getVisibleImgExamsCount() {
            if (!this.search) {
                return imagingCategories.reduce((sum, cat) => sum + cat.exams.length, 0);
            }
            
            return imagingCategories.reduce((sum, cat) => {
                const visible = cat.exams.filter(exam => 
                    exam.name.toLowerCase().includes(this.search.toLowerCase())
                );
                return sum + visible.length;
            }, 0);
        }
    };
}
</script>
