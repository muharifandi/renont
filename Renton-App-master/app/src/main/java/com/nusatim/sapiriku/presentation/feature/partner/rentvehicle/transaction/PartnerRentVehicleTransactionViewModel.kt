package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.transaction
import com.nusatim.sapiriku.core.ui.PagedListViewModel
import com.nusatim.sapiriku.domain.model.RentVehicleTransaction
import com.nusatim.sapiriku.domain.usecase.ListPartnerRentVehicleTransactionsUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import javax.inject.Inject

@HiltViewModel
class PartnerRentVehicleTransactionViewModel @Inject constructor(
    private val listPartnerRentVehicleTransactionsUseCase: ListPartnerRentVehicleTransactionsUseCase
) : PagedListViewModel<RentVehicleTransaction>() {

    var statusSelected: Int = -1

    override suspend fun fetchPage(page: Int, pageSize: Int): Result<List<RentVehicleTransaction>> =
        listPartnerRentVehicleTransactionsUseCase(page, pageSize, statusSelected)

    fun updateStatus(status: Int) {
        statusSelected = status
        loadFirstPage()
    }
}
