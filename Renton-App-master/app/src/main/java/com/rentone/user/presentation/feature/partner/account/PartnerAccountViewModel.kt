package com.rentone.user.presentation.feature.partner.account
import android.net.Uri
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.R
import com.rentone.user.domain.model.PartnerAccountDetail
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.usecase.ChangePartnerBusinessLocationUseCase
import com.rentone.user.domain.usecase.ChangePartnerRegencyUseCase
import com.rentone.user.domain.usecase.GetPartnerDetailUseCase
import com.rentone.user.domain.usecase.RequestPartnerFeatureUseCase
import com.rentone.user.domain.usecase.UploadPartnerProfileImageUseCase
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

    fun uploadProfileImage(uri: Uri) {
        viewModelScope.launch {
            uploadPartnerProfileImageUseCase(uri).collect { resource ->
                when (resource) {
                    is Resource.Success -> loadDetail()
                    is Resource.Error -> {
                        _actionResult.value = PartnerAccountActionResult(R.string.title_partner_profile, resource.message, false)
                        loadDetail()
                    }
                    else -> Unit
                }
            }
        }
    }

    fun changeRegency(regenciesId: Int) {
        viewModelScope.launch {
            changePartnerRegencyUseCase(regenciesId).collect { resource ->
                when (resource) {
                    is Resource.Success -> _actionResult.value = PartnerAccountActionResult(R.string.change_regencies, resource.data.message, true)
                    is Resource.Error -> _actionResult.value = PartnerAccountActionResult(R.string.change_regencies, resource.message, false)
                    else -> Unit
                }
            }
        }
    }

    fun changeLocation(latitude: Double, longitude: Double) {
        viewModelScope.launch {
            changePartnerBusinessLocationUseCase(latitude, longitude).collect { resource ->
                when (resource) {
                    is Resource.Success -> _actionResult.value = PartnerAccountActionResult(R.string.change_bussiness_location, resource.data.message, true)
                    is Resource.Error -> _actionResult.value = PartnerAccountActionResult(R.string.change_bussiness_location, resource.message, false)
                    else -> Unit
                }
            }
        }
    }

    fun requestFeature(featureId: Int) {
        viewModelScope.launch {
            requestPartnerFeatureUseCase(featureId).collect { resource ->
                when (resource) {
                    is Resource.Success -> _actionResult.value = PartnerAccountActionResult(R.string.request_feature, resource.data.message, true)
                    is Resource.Error -> _actionResult.value = PartnerAccountActionResult(R.string.request_feature, resource.message, false)
                    else -> Unit
                }
            }
        }
    }

    fun consumeActionResult() {
        _actionResult.value = null
    }
}
