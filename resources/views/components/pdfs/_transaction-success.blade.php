<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>INVOICE</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- Assuming Tailwind CSS is included, e.g., via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="font-sans text-sm text-[#555555] bg-white w-[19cm] h-[29.7cm] mx-auto">
    <header class="p-[10px] mb-[20px] border-b border-[#AAAAAA] overflow-auto">
        <h2 class="text-center">Reçu de paiement éléctronique</h2>
        <div id="logo" class="float-left mt-2">
            <img class="w-full max-w-[140px] h-[50px]" src="{{ public_path('images/logo_efawtara_dark.png') }}">
        </div>
        {{-- <img class="w-full max-w-[75px] h-[75px] float-right" src="{{ $logo }}"> --}}
    </header>
    <main>
        <div id="invoice-header" class="flex justify-between w-full">
            <div id="details" class="pl-1.5">
                <div id="Signature">
                    <h3>L'entreprise: Guiddini Plus</h3>
                    <div>Télépone: 0540761845</div>
                    <div>Email: contact@efawtara.com </div>
                </div>
            </div>
            <div id="details" class="pl-1.5">
                <div id="Signature">
                    {{-- <h3>bénéficiaire : {{ $entity['name'] }}</h3>
                    <div>Télépone: {{ $entity['phone_number'] }}</div>
                    <div>Email: {{ $entity['email'] }}</div> --}}
                </div>
            </div>
        </div>
        <br><br>
        <h3 class="mt-[90px]">Détails de paiement</h3>
        <table class="w-full border-collapse mb-5">
            <tbody>
                <tr>
                    <td class="p-3 bg-gray-300 text-left w-[150px] border-b border-white">Méthode de paiement</td>
                    <td class="p-3 bg-gray-100 text-right border-b border-white">CIB / Edahabia</td>
                </tr>
                <tr>
                    <td class="p-3 bg-gray-300 text-left w-[150px] border-b border-white">Numéro de commande </td>
                    <td class="p-3 bg-gray-100 text-right border-b border-white">{{ $transaction['order_id'] }}</td>
                </tr>
                <tr>
                    <td class="p-3 bg-gray-300 text-left w-[150px] border-b border-white">ID de transaction </td>
                    <td class="p-3 bg-gray-100 text-right border-b border-white">{{ $transaction['order_number'] }}</td>
                </tr>
                <tr>
                    <td class="p-3 bg-gray-300 text-left w-[150px] border-b border-white">Numéro d'autorisation</td>
                    <td class="p-3 bg-gray-100 text-right border-b border-white">{{ $transaction['auth_code'] }}</td>
                </tr>
                <tr>
                    <td class="p-3 bg-gray-300 text-left w-[150px] border-b border-white">Date et heure</td>
                    <td class="p-3 bg-gray-100 text-right border-b border-white">{{ $transaction['created_at'] }}</td>
                </tr>
                <tr>
                    <td class="p-3 bg-gray-300 text-left w-[150px] border-b border-white">Montant total</td>
                    <td class="p-3 bg-gray-100 text-right border-b border-white">{{ $transaction['amount'] }} DA</td>
                </tr>
            </tbody>
        </table>
    </main>
    <footer class="text-center">
        <h5>Si vous rencontrez un problème avec le paiement, Contactez la SATIM</h5>
        <div>
            <img class="w-full max-w-[140px] h-[50px] block mx-auto" src="{{ public_path('images/numero_vert.png') }}">
        </div>
    </footer>
</body>

</html>