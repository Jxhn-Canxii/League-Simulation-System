export const pageInfo = (page_num, total_rows, items_per_page = 10) => {
    if (page_num < 1) {
        page_num = 1;
    }

    const total_pages = Math.ceil(total_rows / items_per_page);

    if (page_num > total_pages) {
        page_num = total_pages;
    }

    const start_index = (page_num - 1) * items_per_page + 1;
    const end_index = Math.min(page_num * items_per_page, total_rows);

    return `Page ${page_num} of ${total_pages}. Showing ${start_index}-${end_index} of ${total_rows} rows.`;
};

export const page_number = (totalPages, currentPage) => {
    let pagesToShow = [];

    if (totalPages > 0) {
        if (totalPages == currentPage) {
            if (currentPage - 2 > 0) pagesToShow.push(currentPage - 2); // Next page
        }
        if (currentPage > 1) {
            if (currentPage - 1 > 0) pagesToShow.push(currentPage - 1);
        }
        if (currentPage > 0) pagesToShow.push(currentPage);
        if (currentPage == 1 && totalPages < 3) {
            if (currentPage + 1 <= totalPages)
                pagesToShow.push(currentPage + 1); // Next page
        }
        if (currentPage == 1 && totalPages >= 3) {
            if (currentPage + 1 <= totalPages)
                pagesToShow.push(currentPage + 1); // Next page
            if (currentPage + 2 <= totalPages)
                pagesToShow.push(currentPage + 2); // Next page
        }
        if (currentPage < totalPages && currentPage != 1) {
            if (currentPage + 1 <= totalPages)
                pagesToShow.push(currentPage + 1); // Next page
        }
    }
    return pagesToShow;
};

export const generate_page_number = (total_rows, items_per_page) => {
    return Math.ceil(total_rows / items_per_page);
};