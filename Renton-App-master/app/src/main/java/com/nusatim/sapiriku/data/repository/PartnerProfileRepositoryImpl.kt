package com.nusatim.sapiriku.data.repository

import android.content.Context
import com.nusatim.sapiriku.api.service.PartnerService
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.util.FileUtils
import com.nusatim.sapiriku.data.mapper.*
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.PartnerAccountDetail
import com.nusatim.sapiriku.domain.model.command.RegisterPartnerCommand
import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import com.nusatim.sapiriku.domain.repository.PartnerProfileRepository
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PartnerProfileRepositoryImpl @Inject constructor(
    private val partnerService: PartnerService,
    @ApplicationContext private val context: Context
) : BaseRepository(), PartnerProfileRepository {

    override fun register(command: RegisterPartnerCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = {
                val form = mutableMapOf(
                    "company_name" to FileUtils.createPartFromString(command.companyName),
                    "description" to FileUtils.createPartFromString(command.description),
                    "address" to FileUtils.createPartFromString(command.address),
                    "regencies_id" to FileUtils.createPartFromString(command.regencyId.toString())
                )
                command.taxNumber?.let { form["tax_number"] = FileUtils.createPartFromString(it) }
                
                val files = mutableListOf<okhttp3.MultipartBody.Part>()
                command.profileImagePath?.let {
                    files.add(FileUtils.prepareFileImagePart(context, "img_profile", it))
                }
                command.identityImagePath?.let {
                    files.add(FileUtils.prepareFileImagePart(context, "img_identity", it))
                }
                partnerService.register(form, files)
            },
            map = { it.toOperationResult() }
        )
    }

    override fun getDetail(): Flow<Resource<PartnerAccountDetail>> {
        return safeApiCall(
            apiCall = { partnerService.detail() },
            map = { it.toPartnerAccountDetail() }
        )
    }

    override fun changeCompanyName(companyName: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerService.changeCompanyName(mapOf("company_name" to companyName)) },
            map = { it.toOperationResult() }
        )
    }

    override fun changeDescription(description: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerService.changeDescription(mapOf("description" to description)) },
            map = { it.toOperationResult() }
        )
    }

    override fun changeAddress(address: String): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerService.changeAddress(mapOf("address" to address)) },
            map = { it.toOperationResult() }
        )
    }

    override fun changeRegency(regenciesId: Int): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerService.changeRegency(mapOf("regencies_id" to regenciesId.toString())) },
            map = { it.toOperationResult() }
        )
    }

    override fun changeBusinessLocation(latitude: Double, longitude: Double): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = { partnerService.changeBussinessLocation(mapOf("latitude" to latitude.toString(), "longitude" to longitude.toString())) },
            map = { it.toOperationResult() }
        )
    }

    override fun uploadProfileImage(command: UploadImageCommand): Flow<Resource<OperationResult>> {
        return safeApiCall(
            apiCall = {
                val imagePart = FileUtils.prepareFileImagePart(context, "img_profile", command.imagePath)
                partnerService.uploadProfileImage(imagePart)
            },
            map = { it.toOperationResult() }
        )
    }
}
