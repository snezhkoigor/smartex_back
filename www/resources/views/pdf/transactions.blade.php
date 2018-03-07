<html>
<head>
	<style>
		td, th {
			padding: 10px;
		}
		.page-break {
			page-break-after: always;
		}
	</style>
</head>
<body>
	<div class="container">
		<br/>
		<table border="1" style="border-collapse: collapse; font-size: 12px">
			<tr>
				<th></th>
				<th>Date</th>
				<th>Payment System</th>
				<th>Payer</th>
				<th>Payee</th>
				<th>Amount</th>
				<th>Fee</th>
				<th>Comment</th>
				<th>Confirm date</th>
			</tr>
			@foreach ($data['payments'] as $key => $item)
				<tr @if ($item->confirm === 0) style="background-color: indianred" @endif>
					<td>{{ $item->id }}</td>
					<td>{{ date('d.m.Y H:i', strtotime($item->date)) }}</td>
					<td>{{ $item->name }}</td>
					<td>{{ $item->payer ?: '---' }}</td>
					<td>{{ $item->payee ?: '---' }}</td>
					<td>{{ \App\Repositories\CurrencyRepository::getAvailableCurrencies()[strtolower($item->currency)]['name'] . ' ' . $item->amount }}</td>
					<td>{{ $item->fee }}</td>
					<td>{{ $item->comment ?: '---' }}</td>
					<td>{{ date('d.m.y H:i', strtotime($item->date_confirm)) }}</td>
				</tr>
			@endforeach
		</table>
	</div>
</body>
</html>