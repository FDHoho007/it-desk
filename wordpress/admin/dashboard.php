<style>

    #it-desk-stats {
        width: 100%;
        text-align: center;
        border-collapse: collapse;
    }

    #it-desk-stats td, #it-desk-stats th {
        padding: 10px;
    }

    #it-desk-stats table span {
        border-radius: 50%;
        color: white;
        padding: 5px;
    }

    #it-desk-stats .red {
        background-color: lightcoral;
    }

    #it-desk-stats .orange {
        background-color: lightsalmon;
    }

    #it-desk-stats .green {
        background-color: lightgreen;
    }

    #it-desk-stats .grey {
        background-color: lightgrey;
    }

</style>

<table id="it-desk-stats">
    <tr>
        <th>offene Tickets</th>
        <th>Tickets in Bearbeitung</th>
        <th>geschlossene Tickets</th>
    </tr>
    <tr>
        <?php
        function c($count)
        {
            return $count > 10 ? "red" : ($count > 5 ? "orange" : "green");
        }

        $count = [0, 0, 0, 0];
        foreach (ITDesk::getInstance()->getTickets()->getTickets() as $ticket)
            $count[$ticket->getStatus()]++;
        $count[0] += $count[3];
        echo("<td><span class='" . c($count[0]) . "'>$count[0]</span></td><td><span class='" . c($count[0]) . "'>$count[1]</span></td><td><span class='grey'>$count[2]</span></td>");
        ?>
    </tr>
</table>