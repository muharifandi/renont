package com.rentone.user.presentation.feature.partner.account.adapter
import android.content.Context
import android.graphics.BitmapFactory
import android.util.Base64
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import androidx.core.content.ContextCompat
import androidx.core.view.isVisible
import com.rentone.user.R
import com.rentone.user.databinding.ItemListPartnerFeatureBinding
import com.rentone.user.domain.model.PartnerFeature

class ArrayFeatureAdapter(
    private val ctx: Context,
    items: ArrayList<PartnerFeature>,
    private val onActivateClick: (featureId: Int) -> Unit
) : ArrayAdapter<PartnerFeature>(ctx, 0, items) {

    private val partnerFeatures: List<PartnerFeature> = items

    override fun getView(position: Int, convertView: View?, parent: ViewGroup): View {
        val binding = if (convertView == null) {
            ItemListPartnerFeatureBinding.inflate(LayoutInflater.from(ctx), parent, false)
        } else {
            ItemListPartnerFeatureBinding.bind(convertView)
        }

        val data = partnerFeatures[position]

        binding.icon.setImageBitmap(null)
        data.icon?.let {
            val decodedString = Base64.decode(it, Base64.DEFAULT)
            val decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.size)
            binding.icon.setImageBitmap(decodedByte)
        }
        binding.name.text = data.name

        when (data.status) {
            0 -> {
                binding.status.isVisible = false
                binding.btnActivate.isVisible = true
                binding.btnActivate.setOnClickListener { onActivateClick(data.featureId) }
            }
            1 -> {
                binding.status.isVisible = true
                binding.status.text = data.statusName
                binding.status.setTextColor(ContextCompat.getColor(ctx, R.color.green))
                binding.btnActivate.isVisible = false
            }
            2 -> {
                binding.status.isVisible = true
                binding.status.text = data.statusName
                binding.status.setTextColor(ContextCompat.getColor(ctx, R.color.orange))
                binding.btnActivate.isVisible = false
            }
            3 -> {
                binding.status.isVisible = true
                binding.status.text = data.statusName
                binding.status.setTextColor(ContextCompat.getColor(ctx, R.color.red))
                binding.btnActivate.isVisible = false
            }
        }

        return binding.root
    }

    override fun getItem(index: Int): PartnerFeature = partnerFeatures[index]
}
