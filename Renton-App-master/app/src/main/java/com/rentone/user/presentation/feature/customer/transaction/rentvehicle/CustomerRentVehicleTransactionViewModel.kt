package com.rentone.user.presentation.feature.customer.transaction.rentvehicle
import com.rentone.user.core.ui.PagedListViewModel
import com.rentone.user.domain.model.RentVehicleTransaction
import com.rentone.user.domain.usecase.ListCustomerRentVehicleTransactionsUseCase
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
