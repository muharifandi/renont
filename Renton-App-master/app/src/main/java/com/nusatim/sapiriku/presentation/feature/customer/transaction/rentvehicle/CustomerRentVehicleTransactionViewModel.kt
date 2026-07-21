package com.nusatim.sapiriku.presentation.feature.customer.transaction.rentvehicle
import com.nusatim.sapiriku.core.ui.PagedListViewModel
import com.nusatim.sapiriku.domain.model.RentVehicleTransaction
import com.nusatim.sapiriku.domain.usecase.ListCustomerRentVehicleTransactionsUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import javax.inject.Inject

@HiltViewModel
class CustomerRentVehicleTransactionViewModel @Inject constructor(
    private val listCustomerRentVehicleTransactionsUseCase: ListCustomerRentVehicleTransactionsUseCase
) : PagedListViewModel<RentVehicleTransaction>() {

    var statusSelected: Int = -1

    override suspend fun fetchPage(page: Int, pageSize: Int): Result<List<RentVehicleTransaction>> =
        listCustomerRentVehicleTransactionsUseCase(page, pageSize, statusSelected)

    fun updateStatus(status: Int) {
        statusSelected = status
        loadFirstPage()
    }
}
