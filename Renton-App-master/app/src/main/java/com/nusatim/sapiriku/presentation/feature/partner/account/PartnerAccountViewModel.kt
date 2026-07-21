package com.nusatim.sapiriku.presentation.feature.partner.account

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.PartnerAccountDetail
import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import com.nusatim.sapiriku.domain.usecase.*
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class PartnerAccountActionResult(val titleResId: Int, val message: String?, val success: Boolean)

@HiltViewModel
class PartnerAccountViewModel @Inject constructor(
    private val getPartnerDetailUseCase: GetPartnerDetailUseCase,
    private val uploadPartnerProfileImageUseCase: UploadPartnerProfileImageUseCase,
    private val changePartnerRegencyUseCase: ChangePartnerRegencyUseCase,
    private val changePartnerBusinessLocationUseCase: ChangePartnerBusinessLocationUseCase,
    private val requestPartnerFeatureUseCase: RequestPartnerFeatureUseCase
) : ViewModel() {

    private val _detail = MutableStateFlow<UiState<PartnerAccountDetail>>(UiState.Idle)
    val detail = _detail.asStateFlow()

    private val _actionResult = MutableStateFlow<PartnerAccountActionResult?>(null)
    val actionResult = _actionResult.asStateFlow()

    fun loadDetail() {
        viewModelScope.launch {
            getPartnerDetailUseCase().collect { resource ->
                _detail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun uploadProfileImage(uri: android.net.Uri) {
        viewModelScope.launch {
            uploadPartnerProfileImageUseCase(UploadImageCommand(uri.toString())).collect { resource ->
                if (resource is Resource.Success) loadDetail()
            }
        }
    }

    fun changeRegency(regenciesId: Int) {
        viewModelScope.launch {
            changePartnerRegencyUseCase(regenciesId).collect { resource ->
                if (resource is Resource.Success) {
                    _actionResult.value = PartnerAccountActionResult(com.nusatim.sapiriku.R.string.regency, resource.data.message, resource.data.success)
                }
            }
        }
    }

    fun changeLocation(latitude: Double, longitude: Double) {
        viewModelScope.launch {
            changePartnerBusinessLocationUseCase(latitude, longitude).collect { resource ->
                if (resource is Resource.Success) {
                    _actionResult.value = PartnerAccountActionResult(com.nusatim.sapiriku.R.string.set_location, resource.data.message, resource.data.success)
                }
            }
        }
    }

    fun requestFeature(featureId: Int) {
        viewModelScope.launch {
            requestPartnerFeatureUseCase(featureId).collect { resource ->
                if (resource is Resource.Success) {
                    _actionResult.value = PartnerAccountActionResult(com.nusatim.sapiriku.R.string.app_name, resource.data.message, resource.data.success)
                }
            }
        }
    }

    fun consumeActionResult() {
        _actionResult.value = null
    }
}
