<template>
    <div class="flex flex-col items-center w-full">
      <nav
        aria-label="Page navigation example"
        class="flex flex-wrap justify-center mt-5"
      >
        <ul class="flex items-center space-x-1 text-base">
          <li>
            <button
              title="First page"
              @click.prevent="paginateToPage(1)"
              class="flex items-center justify-center px-2 h-10 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
            >
              <span class="sr-only">First</span>
              <i class="fa fa-angle-double-left"></i>
            </button>
          </li>
          <li>
            <button
              title="Previous page"
              :disabled="props.page_number <= 1"
              :class="{ 'opacity-25': props.page_number <= 1 }"
              @click.prevent="paginate(false)"
              class="flex items-center justify-center px-2 h-10 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
            >
              <span class="sr-only">Previous</span>
              <i class="fa fa-angle-left"></i>
            </button>
          </li>
          <li
            v-for="pn in visiblePageNumbers"
            :key="pn"
            :disabled="props.page_number === pn"
            @click.prevent="paginateToPage(pn)"
            :aria-label="`Page ${pn}`"
          >
            <button
              :class="
                props.page_number === pn
                  ? 'text-white bg-yellow-500 hover:bg-yellow-300'
                  : ''
              "
              class="flex items-center justify-center px-3 h-10 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
            >
              {{ pn }}
            </button>
          </li>
          <li>
            <button
              title="Next page"
              :disabled="props.page_number >= totalPages"
              @click.prevent="paginate(true)"
              aria-label="Next Page"
              class="flex items-center justify-center px-2 h-10 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
            >
              <span class="sr-only">Next</span>
              <i class="fa fa-angle-right"></i>
            </button>
          </li>
          <li>
            <button
              title="Last page"
              @click.prevent="paginateToPage(totalPages)"
              aria-label="Page {{ totalPages }}"
              class="flex items-center justify-center px-2 h-10 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
            >
              <span class="sr-only">Last</span>
              <i class="fa fa-angle-double-right"></i>
            </button>
          </li>
        </ul>
      </nav>
      <p v-if="props.total_rows > 0" class="text-bold text-center mt-5">
        {{
          pageInfo(
          props.page_number,
          props.total_rows,
          props.itemsperpage
          )
        }}
      </p>
    </div>
  </template>

  <script setup>
import { ref, computed, defineProps, defineEmits, watch, onMounted } from 'vue';
import { pageInfo, page_number, generate_page_number } from '@/Utility/Pagination';
// Define emits and props
const emits = defineEmits(['page_num']);
const props = defineProps({
  page_number: {
    type: Number,
    default: 1, // Default value for page_number
  },
  total_rows: {
    type: Number,
    default: 0, // Default value for total_rows
  },
  itemsperpage: {
    type: Number,
    default: 10, // Default value for itemsperpage
  },
});

// Define reactive state
const totalPages = ref(0);

// Function to calculate and set total pages
const getTotalPageNumber = () => {
  totalPages.value = generate_page_number(props.total_rows, props.itemsperpage);
};

// Function to handle pagination
const paginate = (isIncrement) => {
  let increment = isIncrement ? props.page_number + 1 : props.page_number - 1;
  emits('page_num', increment);
};

// Function to handle direct page number selection
const paginateToPage = (pageNum) => {
  emits('page_num', pageNum);
};

// Compute visible page numbers
const visiblePageNumbers = computed(() => {
  const currentPage = props.page_number;
  const page = page_number(totalPages.value, currentPage);
  return page;
});

// Watch for changes in total_rows and re-calculate totalPages
watch(() => props.total_rows, (newVal) => {
  getTotalPageNumber();
});

// Watch for changes in itemsperpage if needed
watch(() => props.itemsperpage, () => {
  getTotalPageNumber();
}, { immediate: true });

// Initialize total pages on mount
onMounted(() => {
  getTotalPageNumber();
});
</script>

  <style scoped>
  @media (max-width: 640px) {
    .pagination-container {
      flex-direction: column;
      align-items: center;
    }

    .pagination-controls {
      font-size: 0.875rem; /* Smaller font size for mobile */
    }

    .pagination-controls li {
      margin: 0.25rem; /* Adjust spacing for smaller screens */
    }

    .pagination-controls button {
      padding: 0.5rem;
    }

    .pagination-info {
      font-size: 0.75rem; /* Smaller font size for mobile */
    }
  }
  </style>
