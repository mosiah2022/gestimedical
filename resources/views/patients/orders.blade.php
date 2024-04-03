<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    </head>
<body>
    <h1></h1>

    <table>
        <thead>
            <tr>
                <th colspan="9">RELACIÓN DE PACIENTES SEGUROS BOLIVAR S.A</th>
            </tr>
            <tr>
                <th colspan="9">GESTIMEDICAL SAS . NIT 900.644.246-3</th>
            </tr>
            <tr>
                <th colspan="9">FACTURA # GML - {{ $invoice_number }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $key => $order)
                @php($patientPerOrder = [])
                @foreach ($order as $patient)
                    @if (!in_array($patient->patient->patient_id, $patientPerOrder))
                        <tr>
                            <td rowspan="{{ count($order) }}">{{ $patient->patient->first_name }} {{ $patient->patient->last_name }}</td>
                            <td rowspan="{{ count($order) }}">{{ $patient->patient->personal_id }} </td>
                            <td rowspan="{{ count($order) }}">{{ $patient->order->lm_code }} <br>{{ $patient->order->authorized_by }}</td>
                            <td rowspan="{{ count($order) }}">{{ $patient->order->doctor_name }}</td>
                    @else
                        <tr>
                    @endif
                        <td>{{ $patient->product->name }}</td>
                        <td>{{ $patient->prescription }}</td>
                        <td>
                            @if(empty($patient->product->presentation->name ))
                                {{ $patient->product->presentation_id }}
                            @else
                                {{ $patient->product->presentation->name }}
                            @endif
                        </td>
                        <td data-format="#,##0_-">{{ $patient->product->price,0 }}</td>
                        <td data-format="#,##0_-">{{ $patient->product->price*$patient->prescription,0 }}</td>
                    </tr>
                    {{ $patientPerOrder[] = $patient->patient->patient_id }}
                @endforeach
                <tr>
                    <td style="background-color:#F0F0F0;"></td>
                    <td style="background-color:#F0F0F0;"></td>
                    <td style="background-color:#F0F0F0;"></td>
                    <td style="background-color:#F0F0F0;"></td>
                    <td style="background-color:#F0F0F0;"></td>
                    <td style="background-color:#F0F0F0;"></td>
                    <td style="background-color:#F0F0F0;"></td>
                    <td style="background-color:#F0F0F0;"><strong>VALOR TOTAL FORMULA:</strong></td>
                    <td style="background-color:#F0F0F0;" data-format="$#,##0_-">
                    <strong> {{ array_reduce(
                        $order->toArray(),
                        function ($sum, $patient) {
                            $GLOBALS['total_formula'] = (float) ($sum += (float) ($patient['product']['price']*$patient['prescription']));
                            return $GLOBALS['total_formula'];
                        },
                        0,
                    ) }}</strong>
                    </td>
                    <td></td>
                </tr>

                @if ($patient->order->discount_percent > 0)
                    <tr>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"><strong>COPAGO {{$patient->order->discount_percent}} % POR PARTE DEL USUARIO:</strong></td>
                        <td data-format="$#,##0_-" style="background-color:#F0F0F0; color:#FF0000;">
                        <strong>{{ array_reduce(
                            $order->toArray(),
                            function ($sum, $patient) {
                                $x = round($GLOBALS['total_formula']*($patient['order']['discount_percent']/100),2);
                                return $x;
                            },
                            0,
                        ) }}</strong>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"></td>
                        <td style="background-color:#F0F0F0;"><strong>VALOR TOTAL FORMULA:</strong></td>
                        <td style="background-color:#F0F0F0;" data-format="$#,##0_-">
                        <strong> {{ array_reduce(
                            $order->toArray(),
                            function ($sum, $patient) {
                                $x = $patient['order']['discount_percent'] > 0 ? round($GLOBALS['total_formula']-($GLOBALS['total_formula']*($patient['order']['discount_percent']/100)),2) : $x;
                                return $x;
                            },
                            0,
                        ) }}</strong>
                        </td>
                        <td></td>
                    </tr>
                @endif
                <tr><td></td></tr>
            @endforeach
        </tbody>
        <tfoot>
            <th></th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><strong>VALOR TOTAL FACTURA: </strong></td>
            <td data-format="$#,##0_-"><strong>
                {{
                    $orders->map(function($order) {
                        return array_reduce(
                            $order->toArray(),
                            function ($sum, $patient) {
                                $total_invoice = ($sum += $patient['product']['price']*$patient['prescription']);
                                $general_invoice = $patient['order']['discount_percent'] > 0 ? round($total_invoice-($total_invoice*($patient['order']['discount_percent']/100)),2) : $total_invoice;
                                return $general_invoice;
                            },
                            0,
                        );
                    })->reduce(function($a, $b){
                            return $a+$b;
                        })
                }}
                </strong>
            </td>

        </tfoot>
    </table>

</body>

</html>
