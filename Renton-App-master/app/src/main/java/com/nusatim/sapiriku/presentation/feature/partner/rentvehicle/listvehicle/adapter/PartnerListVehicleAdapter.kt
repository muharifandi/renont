package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.listvehicle.adapter
import android.graphics.Color
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.view.isVisible
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.core.common.Config
import com.nusatim.sapiriku.core.ui.LoadingFooterListAdapter
import com.nusatim.sapiriku.databinding.ItemPartnerListVehicleBinding
import com.nusatim.sapiriku.domain.model.Vehicle
import java.text.NumberFormat
import java.util.Locale

class PartnerListVehicleAdapter(
    private val onItemClick: (Vehicle) -> Unit
) : LoadingFooterListAdapter<Vehicle>(DIFF_CALLBACK) {

    override fun onCreateItemViewHolder(parent: ViewGroup): RecyclerView.ViewHolder =
        VehicleViewHolder(ItemPartnerListVehicleBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindItemViewHolder(holder: RecyclerView.ViewHolder, item: Vehicle, position: Int) {
        (holder as VehicleViewHolder).bind(item)
    }

    inner class VehicleViewHolder(private val binding: ItemPartnerListVehicleBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(item: Vehicle) {
            if (item.img != null) {
                binding.previewVehicle.load(Config.BASE_VEHICLE_IMAGE + "thumb_" + item.img) {
                    error(R.drawable.ic_broken_image)
                }
            } else {
                binding.previewVehicle.setImageResource(R.drawable.no_image)
            }

            binding.txtTitle.text = item.title
            binding.txtVehicleType.text = item.vehicleTypeName
            binding.containerWIthDriver.isVisible = item.withDriver == 1
            binding.txtMaxPassenger.text = item.maxPassenger.toString()

            binding.containerColor.isVisible = item.colorName != null
            if (item.colorName != null) {
                binding.txtColor.text = item.colorName
                binding.colorBox.setCardBackgroundColor(Color.parseColor(item.colorValue))
            }

            binding.txtPrice.text = "Rp. ${NumberFormat.getNumberInstance(Locale.GERMANY).format(item.price)},-"

            binding.itemContainer.setOnClickListener { onItemClick(item) }
        }
    }

    private companion object {
        val DIFF_CALLBACK = object : DiffUtil.ItemCallback<Vehicle>() {
            override fun areItemsTheSame(oldItem: Vehicle, newItem: Vehicle) = oldItem.id == newItem.id
            override fun areContentsTheSame(oldItem: Vehicle, newItem: Vehicle) = oldItem == newItem
        }
    }
}
