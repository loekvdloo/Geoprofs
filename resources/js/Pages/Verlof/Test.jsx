import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, useForm } from '@inertiajs/react'

export default function Test({ auth, types }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        verlof_type_id: '',
        start_datum: '',
        eind_datum: '',
        reden: '',
    })

    function submit(e) {
        e.preventDefault()
        post('/verlof/aanvragen', { onSuccess: () => reset() })
    }

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Verlofaanvraag Test" />
            <div className="max-w-xl mx-auto p-6 space-y-4">
                <h1 className="text-2xl font-semibold">Verlofaanvraag</h1>

                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium">Verloftype</label>
                        <select
                            name="verlof_type_id"
                            value={data.verlof_type_id}
                            onChange={e => setData('verlof_type_id', e.target.value)}
                            className="mt-1 w-full rounded border p-2"
                            required
                        >
                            <option value="">— Kies —</option>
                            {types.map(t => (
                                <option key={t.verlof_type_id} value={t.verlof_type_id}>
                                    {t.naam} {t.betaald ? '(betaald)' : '(onbetaald)'}
                                </option>
                            ))}
                        </select>
                        {errors.verlof_type_id && <p className="text-red-600 text-sm">{errors.verlof_type_id}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Startdatum</label>
                            <input type="date" name="start_datum" value={data.start_datum}
                                   onChange={e => setData('start_datum', e.target.value)}
                                   className="mt-1 w-full rounded border p-2" required/>
                            {errors.start_datum && <p className="text-red-600 text-sm">{errors.start_datum}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Einddatum</label>
                            <input type="date" name="eind_datum" value={data.eind_datum}
                                   onChange={e => setData('eind_datum', e.target.value)}
                                   className="mt-1 w-full rounded border p-2" required/>
                            {errors.eind_datum && <p className="text-red-600 text-sm">{errors.eind_datum}</p>}
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Reden</label>
                        <textarea name="reden" rows="3" value={data.reden}
                                  onChange={e => setData('reden', e.target.value)}
                                  className="mt-1 w-full rounded border p-2" required/>
                        {errors.reden && <p className="text-red-600 text-sm">{errors.reden}</p>}
                    </div>

                    <button disabled={processing}
                            className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        {processing ? 'Bezig…' : 'Indienen'}
                    </button>
                </form>
            </div>
        </AuthenticatedLayout>
    )
}
