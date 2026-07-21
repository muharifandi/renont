package com.nusatim.sapiriku.presentation.feature.home.adapter

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.view.isVisible
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.core.common.Config
import com.nusatim.sapiriku.databinding.ItemListRecomendationRentvehicleBinding
import com.nusatim.sapiriku.domain.model.Vehicle
import java.text.NumberFormat
import java.util.Locale

class ListRecomendationRentVehicleAdapter(
    private val onItemClick: (Vehicle) -> Unit
) : ListAdapter<Vehicle, ListRecomendationRentVehicleAdapter.VehicleViewHolder>(DIFF_CALLBACK) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VehicleViewHolder =
        VehicleViewHolder(ItemListRecomendationRentvehicleBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindViewHolder(holder: VehicleViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    inner class VehicleViewHolder(
        private val binding: ItemListRecomendationRentvehicleBinding
    ) : RecyclerView.ViewHolder(binding.root) {
        fun bind(item: Vehicle) {
            binding.cvHighlight.isVisible = item.promote == 1

            if (item.img != null) {
                binding.previewVehicle.load(Config.BASE_VEHICLE_IMAGE + "thumb_" + item.img) {
                    error(R.drawable.ic_broken_image)
                }
            } else {
                binding.previewVehicle.setImageResource(R.drawable.no_image)
            }

            binding.txtTitle.text = item.title
            binding.txtRegencies.text = item.regenciesName
            binding.txtRating.text = "(${item.totalReview})"
            binding.ratingBar.rating = item.rating.toFloat()

            val price = if (item.withDriver == 1) item.priceWithDriverBasic else item.price
            binding.txtPrice.text = "Rp. ${NumberFormat.getNumberInstance(Locale.GERMANY).format(price)},-"

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
