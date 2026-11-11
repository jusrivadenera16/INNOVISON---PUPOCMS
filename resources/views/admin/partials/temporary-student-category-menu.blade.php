<div class="temp-student-category-menu-wrap" data-temp-category-menu-wrap hidden>
    <button type="button" class="verify-proposed-withdraw temp-student-category-footer-btn" data-temp-category-open aria-expanded="false" aria-controls="temporaryStudentCategoryBubble">Change Form Category</button>
    <div id="temporaryStudentCategoryBubble" class="temp-category-bubble" popover="manual" role="dialog" aria-labelledby="tempCategoryTitle" hidden>
        <header><strong id="tempCategoryTitle">Change Form Category</strong><button type="button" data-temp-category-close aria-label="Close">&times;</button></header>
        <p data-temp-category-current></p>
        <form data-temp-category-form>
            <label id="tempStudentCategoryLabel" for="tempStudentCategorySelect">Health Form Category</label>
            <select id="tempStudentCategorySelect" data-temp-category-select required disabled></select>
            <button type="button" class="category-bubble-trigger" data-temp-category-category-trigger aria-labelledby="tempStudentCategoryLabel tempStudentCategoryValue" aria-haspopup="listbox" aria-controls="tempStudentCategoryOptions" aria-expanded="false" hidden><span id="tempStudentCategoryValue">Select a category</span></button>
            <div id="tempStudentCategoryOptions" class="category-bubble-options" data-temp-category-category-menu role="listbox" aria-labelledby="tempStudentCategoryLabel" hidden></div>
            <small>Updates the category and PDFs without changing uploads or approval status.</small>
            <p data-temp-category-status role="status" hidden></p>
            <button type="submit" data-temp-category-apply disabled>Apply</button>
        </form>
    </div>
</div>
