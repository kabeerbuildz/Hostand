// locale-aware main JS (dynamic loading of translations)
(function () {
    "use strict";

    // ---------- CONFIG / LOCALE DETECTION ----------
    // Expects Blade to set: <script>var appLocale = "{{ app()->getLocale() }}";</script>
    var locale = (window && window.appLocale) || document.documentElement.lang || "en";

    // Normalize codes
    if (locale.indexOf("-") !== -1) {
        locale = locale.split(/[-_]/)[0];
    }

    // Map full names to ISO codes
    var localeMap = {
        english: "en",
        italian: "it",
        french: "fr",
        spanish: "es"
    };
    if (localeMap[locale.toLowerCase()]) {
        locale = localeMap[locale.toLowerCase()];
    }

    // ---------- EMBEDDED TRANSLATIONS ----------
    var translations = {
        en: {
            datatables: {
                emptyTable: "No data available in table",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                lengthMenu: "Show _MENU_ entries",
                loadingRecords: "Loading...",
                processing: "Processing...",
                search: "Search:",
                zeroRecords: "No matching records found",
                paginate: { first: "First", last: "Last", next: "Next", previous: "Previous" },
                aria: { sortAscending: ": activate to sort column ascending", sortDescending: ": activate to sort column descending" }
            },
            buttons: { excel: "Excel", pdf: "PDF", copy: "Copy", colvis: "Column visibility", csv: "CSV", print: "Print" },
            swal: {
                confirmButtonText: "Yes",
                cancelButtonText: "Cancel",
                deleteTitle: "Are you sure?",
                deleteText: "This item cannot be restored after delete. Do you want to confirm?",
                commonTitlePrefix: "Are you sure you want to delete ",
                commonTextSuffix: " can not be restore after delete. Do you want to confirm?"
            },
            toasts: { successTitle: "Success!", errorTitle: "Error!" }
        },
        it: {
            datatables: {
                emptyTable: "Nessun dato disponibile nella tabella",
                info: "Visualizzazione da _START_ a _END_ di _TOTAL_ voci",
                infoEmpty: "Visualizzazione da 0 a 0 di 0 voci",
                infoFiltered: "(filtrato da _MAX_ voci totali)",
                lengthMenu: "Mostra _MENU_ voci",
                loadingRecords: "Caricamento...",
                processing: "Elaborazione...",
                search: "Cerca:",
                zeroRecords: "Nessun record corrispondente trovato",
                paginate: { first: "Primo", last: "Ultimo", next: "Successivo", previous: "Precedente" },
                aria: { sortAscending: ": attiva per ordinare la colonna in ordine crescente", sortDescending: ": attiva per ordinare la colonna in ordine decrescente" }
            },
            buttons: { excel: "Excel", pdf: "PDF", copy: "Copia", colvis: "Visibilità colonne", csv: "CSV", print: "Stampa" },
            swal: {
                confirmButtonText: "Sì",
                cancelButtonText: "Annulla",
                deleteTitle: "Sei sicuro?",
                deleteText: "Questo elemento non può essere ripristinato dopo l'eliminazione. Vuoi confermare?",
                commonTitlePrefix: "Sei sicuro di voler eliminare ",
                commonTextSuffix: " non può essere ripristinato dopo l'eliminazione. Vuoi confermare?"
            },
            toasts: { successTitle: "Successo!", errorTitle: "Errore!" }
        }
    };

    // ---------- DYNAMIC LOADING HELPERS ----------
    function fetchExternalTranslations(lang) {
        return new Promise(function (resolve, reject) {
            var url = "/assets/lang/datatables/" + encodeURIComponent(lang) + ".json";
            $.ajax({
                url: url,
                dataType: "json",
                success: function (data) { resolve(data); },
                error: function () { reject(); }
            });
        });
    }

    function resolveTranslations(lang) {
        return new Promise(function (resolve) {
            if (translations[lang]) {
                resolve(translations[lang]);
            } else {
                fetchExternalTranslations(lang)
                    .then(function (data) {
                        translations[lang] = data;
                        resolve(translations[lang]);
                    })
                    .catch(function () {
                        resolve(translations["en"]);
                    });
            }
        });
    }

    // ---------- INIT WITH TRANSLATIONS ----------
    function initAppWithTranslations(t) {
        $(document).ready(function () {
            select2();
            datatable(t);
            ckediter();
            setInterval(function () { feather.replace(); }, 1000);
        });

        $(document).on("click", ".customModal", function () {
            var modalTitle = $(this).data("title");
            var modalUrl = $(this).data("url");
            var modalSize = $(this).data("size") === "" ? "md" : $(this).data("size");
            $("#customModal .modal-title").html(modalTitle);
            $("#customModal .modal-dialog").addClass("modal-" + modalSize);
            $.ajax({
                url: modalUrl,
                success: function (result) {
                    if (result.status === "error") {
                        notifier.show(t.toasts.errorTitle, result.messages, "error", errorImg, 4000);
                    } else {
                        $("#customModal .body").html(result);
                        $("#customModal").modal("show");
                        select2();
                        ckediter();
                    }
                }
            });
        });

        $(document).on("click", ".confirm_dialog", function (e) {
            e.preventDefault();
            var title = $(this).attr("data-dialog-title") || window.deleteConfirmTitle || t.swal.deleteTitle;
            var text = $(this).attr("data-dialog-text") || window.deleteConfirmText || t.swal.deleteText;
            var dialogForm = $(this).closest("form");
            Swal.fire({
                title: title,
                text: text,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: t.swal.confirmButtonText,
                cancelButtonText: t.swal.cancelButtonText
            }).then(function (data) { if (data.isConfirmed) dialogForm.submit(); });
        });

        $(document).on("click", ".common_confirm_dialog", function (e) {
            e.preventDefault();
            var dialogForm = $(this).closest("form");
            var actions = $(this).data("actions") || "";
            var title = (t.swal.commonTitlePrefix || "") + actions + " ?";
            var combinedText = locale === "it" ?
                (t.swal.commonTitlePrefix || "") + actions + ". " + (t.swal.commonTextSuffix || "") :
                (t.swal.commonTitlePrefix || "") + actions + " ?" + " " + (t.swal.commonTextSuffix || "");

            Swal.fire({
                title: title,
                text: combinedText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: t.swal.confirmButtonText,
                cancelButtonText: t.swal.cancelButtonText
            }).then(function (data) { if (data.isConfirmed) dialogForm.submit(); });
        });

        $(document).on("click", ".fc-day-grid-event", function (e) {
            e.preventDefault();
            var modalTitle = $(this).find(".fc-content .fc-title").html();
            var modalSize = "md";
            var modalUrl = $(this).attr("href");
            $("#customModal .modal-title").html(modalTitle);
            $("#customModal .modal-dialog").addClass("modal-" + modalSize);
            $.ajax({ url: modalUrl, success: function (result) {
                $("#customModal .modal-body").html(result);
                $("#customModal").modal("show");
            }});
        });

        function toastrs(title, message, status) {
            if (status === "success") {
                notifier.show(t.toasts.successTitle, message, "success", successImg, 4000);
            } else {
                notifier.show(t.toasts.errorTitle, message, "error", errorImg, 4000);
            }
        }

        function convertArrayToJson(form) {
            var data = $(form).serializeArray();
            var indexed_array = {};
            $.map(data, function (n) { indexed_array[n["name"]] = n["value"]; });
            return indexed_array;
        }

        function select2() {
            if ($(".basic-select").length > 0) $(".basic-select").each(function () { new Choices(this, { searchEnabled: false, removeItemButton: false }); });
            if ($(".hidesearch").length > 0) $(".hidesearch").each(function () { new Choices(this, { searchEnabled: false, removeItemButton: true }); });
        }

        function ckediter(editer_id) {
            editer_id = editer_id || "#classic-editor";
            if ($(editer_id).length > 0) ClassicEditor.create(document.querySelector(editer_id)).catch(console.error);
        }

        function datatable(tLocal) {
            if ($(".basic-datatable").length > 0) {
                $(".basic-datatable").DataTable({
                    scrollX: true,
                    dom: "Bfrtip",
                    buttons: [
                        { extend: "copyHtml5", text: tLocal.buttons.copy },
                        { extend: "csvHtml5", text: tLocal.buttons.csv },
                        { extend: "excelHtml5", text: tLocal.buttons.excel },
                        { extend: "print", text: tLocal.buttons.print }
                    ],
                    language: tLocal.datatables
                });
            }

            if ($(".advance-datatable").length > 0) {
                $(".advance-datatable").DataTable({
                    scrollX: true,
                    stateSave: false,
                    dom: "Bfrtip",
                    buttons: [
                        { extend: "excelHtml5", text: tLocal.buttons.excel, exportOptions: { columns: ":visible" } },
                        { extend: "pdfHtml5", text: tLocal.buttons.pdf, exportOptions: { columns: ":visible" } },
                        { extend: "copyHtml5", text: tLocal.buttons.copy, exportOptions: { columns: ":visible" } },
                        { extend: "colvis", text: tLocal.buttons.colvis }
                    ],
                    language: tLocal.datatables
                });
            }
        }

        window.appDatatableInit = function () { datatable(t); };
        window.appToastrs = toastrs;
    }

    resolveTranslations(locale).then(function (t) { initAppWithTranslations(t); }).catch(function () { initAppWithTranslations(translations["en"]); });

})();
