<div class="wrap">

    <h1 class="wp-heading-inline">Ticketarchiv</h1><br><br>

    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
        <tr>
            <th>ID</th>
            <th>Gerät</th>
            <th>Problem</th>
            <th>gemeldet von</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach (ITDesk::getInstance()->getTickets()->getTickets() as $ticket)
            if ($ticket->canView() && $ticket->getStatus() == 2) {
                ?>
                <tr>
                    <td>
                        <strong><a href="/ticket/<?php echo($ticket->getId()); ?>"><?php echo($ticket->getId()); ?></a></strong>
                    </td>
                    <td>
                        <strong><a href="/device/<?php echo($ticket->getDevice()->getId()); ?>"><?php echo(Constants::TYPES[$ticket->getDevice()->getModel()->getType()] . ($ticket->getDevice() instanceof Nameable && $ticket->getDevice()->getName() != null ? " (" . $ticket->getDevice()->getName() . ")" : "") . " Raum " . $ticket->getDevice()->getLocation()->getId()); ?></a></strong>
                    </td>
                    <td>
                        <strong><a href="/ticket/<?php echo($ticket->getId()); ?>"><?php echo($ticket->getIssue()->getTitle()); ?></a></strong>
                    </td>
                    <td>
                        <strong><?php echo($ticket->getAuthor() == -1 ? "Anonym" : get_user_by("ID", $ticket->getAuthor())->display_name); ?></strong>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
        <tr>
            <th>ID</th>
            <th>Gerät</th>
            <th>Problem</th>
            <th>gemeldet von</th>
        </tr>
        </tfoot>
    </table>

</div>