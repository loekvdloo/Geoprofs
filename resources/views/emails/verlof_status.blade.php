<!DOCTYPE html>
<html>

<body>
    <h2>Update over je verlofaanvraag</h2>
    <p>Beste {{ $aanvraag->medewerker->name }},</p>
    <p>Je verlofaanvraag voor <strong>{{ $aanvraag->type->naam }}</strong> van
        <strong>{{ $aanvraag->start_datum }}</strong> tot <strong>{{ $aanvraag->eind_datum }}</strong> is
        <strong>{{ $status === 'accepted' ? 'goedgekeurd' : 'afgekeurd' }}</strong>.
    </p>
    <p>Reden: {{ $aanvraag->reden }}</p>
</body>

</html>