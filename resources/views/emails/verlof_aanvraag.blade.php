<!DOCTYPE html>
<html>

<body>
    <h2>Je verlofaanvraag is ingediend</h2>
    <p><strong>Type verlof:</strong> {{ $aanvraag->type->naam }}</p>
    <p><strong>Periode:</strong> {{ $aanvraag->start_datum }} tot {{ $aanvraag->eind_datum }}</p>
    <p><strong>Reden:</strong> {{ $aanvraag->reden }}</p>
    <p>Status: {{ $aanvraag->status }}</p>
</body>

</html>