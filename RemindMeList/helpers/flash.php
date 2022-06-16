<?php if (isset($_SESSION["flash_message"])) {
    echo('<header>
            <div class="alert mb-0 text-center" id="flashMessage" role="alert">
                ' . $_SESSION["flash_message"] . '
            </div>
        </header>');
        unset($_SESSION["flash_message"]);
}