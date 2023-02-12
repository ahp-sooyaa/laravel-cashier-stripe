<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Charge') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session()->has('success'))
                        <div class="mb-3 rounded-md bg-green-100 px-4 py-2 text-green-500">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="mb-3 rounded-md bg-red-100 px-4 py-2 text-red-500">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($invoices->count() > 0)
                        <table class="mb-4 w-1/2 table-auto overflow-hidden rounded bg-gray-100 text-left shadow">
                            <thead class="border-b border-gray-300 text-gray-500">
                                <tr>
                                    <th class="p-3">Amount</th>
                                    <th class="p-3">Created at</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-500">
                                @foreach ($invoices as $invoice)
                                    <tr class="odd:bg-white even:bg-gray-100">
                                        <td class="p-3">{{ $invoice->total() }}</td>
                                        <td class="p-3">{{ $invoice->date()->toFormattedDateString() }}</td>
                                        <td class="flex items-center py-2">
                                            <a href="/user/invoice/{{ $invoice->id }}" class="rounded bg-white p-1 shadow">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <h1>
                        One time payment
                    </h1>

                    <form action="{{ route('charge.post') }}" method="POST" id="payment-form" class="w-1/2">
                        @csrf

                        <input id="card-holder-name" type="text" placeholder="Card Holder Name"
                            class="mb-3 rounded-md border-gray-300 text-xs">

                        <div id="card-element" class="rounded-md border p-2 shadow">
                            <!-- Elements will create input elements here -->
                        </div>

                        <!-- We'll put the error messages in this element -->
                        <div id="card-errors" role="alert" class="mt-1 text-sm text-red-500"></div>

                        <x-primary-button class="mt-3">Submit Payment</x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script type="module">
            // Set your publishable key: remember to change this to your live publishable key in production
            // See your keys here: https://dashboard.stripe.com/apikeys
            // console.log(import.meta); need to define 'type="module"' otherwise import.meta show syntax error
            const stripe = Stripe('pk_test_51JnarjK3AEGA3BHp6n9Urmu60LR702RMXQVWzOj3uN7KY6IT9HIQLn7uoFi7rzO3b41glf7kRJSQJ1VBAyElLg1100etmH6ltD');

            // Set up Stripe.js and Elements to use in checkout form
            const elements = stripe.elements();
            const style = {
                base: {
                    iconColor: '#32325d',
                    color: "#32325d",
                    fontWeight: '300',
                    fontSize: '14px',
                    fontSmoothing: 'antialiased',
                },
                invalid: {
                    iconColor: '#e92121',
                    color: '#e92121',
                },
            };

            const card = elements.create("card", { style: style });
            card.mount("#card-element");

            card.on('change', ({error}) => {
                let displayError = document.getElementById('card-errors');
                if (error) {
                    displayError.textContent = error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            // handle form submission
            const form = document.getElementById('payment-form');
            const cardHolderName = document.getElementById('card-holder-name');

            form.addEventListener('submit', async (ev) => {
                ev.preventDefault();
                // If the client secret was rendered server-side as a data-secret attribute
                // on the <form> element, you can retrieve it here by calling `form.dataset.secret`
                const { paymentMethod, error } = await stripe.createPaymentMethod('card', card, {
                    billing_details: { 
                        name: cardHolderName.value 
                    }
                });

                if (error) {
                    // Show error to your customer (for example, insufficient funds)
                    // this is the same with 'card.on('change')'
                    let displayError = document.getElementById('card-errors');
                    displayError.textContent = error.message;
                } else {
                    // The payment has been processed!
                    console.log(paymentMethod);
                    // Show a success message to your customer
                    // There's a risk of the customer closing the window before callback
                    // execution. Set up a webhook or plugin to listen for the
                    // payment_intent.succeeded event that handles any business critical
                    // post-payment actions.
                    const hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'payment_method');
                    hiddenInput.setAttribute('value', paymentMethod.id);

                    form.appendChild(hiddenInput);

                    // submit form
                    form.submit();
                }
            });
        </script>
    @endpush
</x-app-layout>
