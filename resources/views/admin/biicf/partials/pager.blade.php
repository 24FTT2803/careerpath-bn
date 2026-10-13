{{--
    The pager for the admin listings. Counts, a page size, and
    controls that wrap rather than overflow a narrow screen.
--}}
<div class="pager">
    <div class="pager-count">
        <span x-show="filtered.length">
            Showing <span x-text="from"></span>&ndash;<span x-text="to"></span>
            of <span x-text="filtered.length"></span>
        </span>
        <span x-show="!filtered.length">Nothing to show</span>

        <select x-model.number="size" @change="page = 1" aria-label="Rows per page">
            <option value="15">15 per page</option>
            <option value="30">30 per page</option>
            <option value="60">60 per page</option>
            <option value="200">200 per page</option>
        </select>
    </div>

    <div class="pager-pages" x-show="pages > 1">
        <button type="button" @click="go(current - 1)" :disabled="current === 1" aria-label="Previous page">
            <i class="fas fa-chevron-left"></i>
        </button>

        <span class="pager-of">
            Page <span x-text="current"></span> of <span x-text="pages"></span>
        </span>

        <template x-for="(number, index) in numbers" :key="number">
            <span class="pager-number-wrap">
                <span class="pager-gap" x-show="index > 0 && number - numbers[index - 1] > 1">&hellip;</span>
                <button type="button" class="pager-number"
                        :class="{ 'is-current': number === current }"
                        @click="go(number)" x-text="number"></button>
            </span>
        </template>

        <button type="button" @click="go(current + 1)" :disabled="current === pages" aria-label="Next page">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
