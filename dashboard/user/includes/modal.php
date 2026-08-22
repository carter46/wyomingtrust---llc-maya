<!-- Custom Modal -->
<div id="customModal" class="hidden fixed inset-0 z-[70] overflow-y-auto">
<div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
<div class="fixed inset-0 bg-primary/40 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
<div class="inline-block align-bottom bg-surface-container-lowest rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-outline-variant">
<div class="px-4 pt-5 pb-4 sm:p-6">
<div class="sm:flex sm:items-start">
<div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-secondary/10 sm:mx-0 sm:h-10 sm:w-10">
<span id="modalIcon"><?php echo wt_icon('info', 'text-secondary text-xl'); ?></span>
</div>
<div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
<h3 id="modalTitle" class="text-lg leading-6 font-bold text-primary"></h3>
<div class="mt-2">
<p id="modalMessage" class="text-sm text-on-surface-variant"></p>
<div id="modalInput" class="hidden mt-4">
<input type="text" id="modalInputField" class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface-container-low text-on-surface" placeholder="">
</div>
</div>
</div>
</div>
</div>
<div class="bg-surface-container-low px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
<button id="modalConfirmBtn" type="button" class="w-full inline-flex justify-center rounded-lg px-4 py-2 bg-primary text-on-primary text-base font-bold hover:bg-primary/90 sm:ml-3 sm:w-auto sm:text-sm"></button>
<button id="modalCancelBtn" type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-outline-variant px-4 py-2 bg-surface-container-lowest text-on-surface font-semibold hover:bg-surface-container sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
</div>
</div>
</div>
</div>
